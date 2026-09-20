<?php
/**
 * Pre-computed waveform peaks.
 *
 * WaveSurfer only skips downloading the whole file when it is handed peaks
 * up front, so this generates them once per attachment with ffmpeg and caches
 * them in post meta. Everything here is optional: with no ffmpeg on the
 * server, nothing is stored and the player falls back to decoding in the
 * browser exactly as before.
 */

if (! defined('ABSPATH')) exit;

/* Mono downmix rate used only for measuring amplitude, not for playback. */
define('ELIN_AUDIO_PEAK_RATE', 4000);

/* Number of bars in the waveform. 1000 floats is roughly 4KB of JSON. */
define('ELIN_AUDIO_PEAK_COUNT', 1000);

define('ELIN_AUDIO_PEAK_META', '_elin_audio_peaks');

/**
 * Path to a working ffmpeg, or '' when there is none.
 *
 * Probing shells out, so the answer is cached for a day — long enough to stay
 * off the request path, short enough that installing ffmpeg later is picked up
 * without clearing anything by hand.
 */
function elin_audio_ffmpeg_path()
{

    $override = apply_filters('elin_audio_ffmpeg_path', null);

    if (null !== $override) return (string) $override;

    $cached = get_transient('elin_audio_ffmpeg');

    if (false !== $cached) return $cached;

    $path = '';

    if (elin_audio_can_shell_exec()) {

        $version = @shell_exec('ffmpeg -version 2>&1');

        if (is_string($version) && false !== stripos($version, 'ffmpeg version')) {
            $path = 'ffmpeg';
        }
    }

    set_transient('elin_audio_ffmpeg', $path, DAY_IN_SECONDS);

    return $path;
}

/**
 * Many hosts disable shell_exec, and calling it anyway raises a warning.
 */
function elin_audio_can_shell_exec()
{

    if (! function_exists('shell_exec')) return false;

    $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

    return ! in_array('shell_exec', $disabled, true);
}

/**
 * Cached peaks for an attachment, or null when the player should fall back.
 *
 * A miss schedules generation rather than running ffmpeg inline: the visitor
 * who happens to be first should not pay for a decode of the whole episode.
 */
function elin_audio_peaks_for_attachment($attachment_id)
{

    $data = get_post_meta($attachment_id, ELIN_AUDIO_PEAK_META, true);

    if (is_array($data) && ! empty($data['peaks']) && ! empty($data['duration'])) {
        return $data;
    }

    /* A previous run already failed on this file; do not retry every render. */
    if ('failed' === $data) return null;

    if (! elin_audio_ffmpeg_path()) return null;

    if (! wp_next_scheduled('elin_audio_generate_peaks', [$attachment_id])) {
        wp_schedule_single_event(time(), 'elin_audio_generate_peaks', [$attachment_id]);
    }

    return null;
}

/**
 * Cron callback: decode the attachment down to amplitudes and store them.
 */
function elin_audio_generate_peaks($attachment_id)
{

    $attachment_id = (int) $attachment_id;

    $file = get_attached_file($attachment_id);

    if (! $file || ! file_exists($file)) return;

    $ffmpeg = elin_audio_ffmpeg_path();

    if (! $ffmpeg) return;

    $raw = wp_tempnam('elin-peaks');

    if (! $raw) return;

    /* Mono, low rate, headerless signed 16-bit — the smallest thing that still
       carries the envelope we draw. */
    $command = sprintf(
        '%s -v quiet -y -i %s -ac 1 -ar %d -f s16le %s 2>&1',
        escapeshellcmd($ffmpeg),
        escapeshellarg($file),
        ELIN_AUDIO_PEAK_RATE,
        escapeshellarg($raw)
    );

    @shell_exec($command);

    $peaks = elin_audio_extract_peaks($raw, ELIN_AUDIO_PEAK_COUNT);

    $samples = file_exists($raw) ? (int) floor(filesize($raw) / 2) : 0;

    @unlink($raw);

    if (empty($peaks)) {
        update_post_meta($attachment_id, ELIN_AUDIO_PEAK_META, 'failed');
        return;
    }

    update_post_meta($attachment_id, ELIN_AUDIO_PEAK_META, [
        'peaks' => $peaks,
        /* Sample count over the rate we asked for: exact, and free. */
        'duration' => round($samples / ELIN_AUDIO_PEAK_RATE, 3),
        'version' => ELIN_AUDIO_VERSION,
    ]);
}

add_action('elin_audio_generate_peaks', 'elin_audio_generate_peaks');

/**
 * Reduce raw signed 16-bit little-endian mono PCM to one value per bar.
 *
 * Matches WaveSurfer's own exportPeaks(): per bucket keep the sample with the
 * largest absolute value *and its sign*, so the rendered waveform is not
 * folded into the upper half.
 *
 * @return array<float> Values in -1..1, empty when the input is unusable.
 */
function elin_audio_extract_peaks($file, $buckets)
{

    if (! is_readable($file)) return [];

    /* wp_tempnam() creates the file empty before ffmpeg writes to it, so a
       cached stat from that moment would report zero samples. */
    clearstatcache(true, $file);

    $total = (int) floor(filesize($file) / 2);

    if ($total < $buckets || $buckets < 1) return [];

    $handle = fopen($file, 'rb');

    if (! $handle) return [];

    $peaks = [];
    $per_bucket = $total / $buckets;

    for ($i = 0; $i < $buckets; $i++) {

        $start = (int) floor($i * $per_bucket);
        $count = max(1, (int) ceil(($i + 1) * $per_bucket) - $start);

        fseek($handle, $start * 2);

        $chunk = fread($handle, $count * 2);

        if (false === $chunk || strlen($chunk) < 2) break;

        /* Trim a trailing odd byte so unpack() never reads past the sample. */
        $values = unpack('v*', substr($chunk, 0, strlen($chunk) & ~1));

        $peak = 0;

        foreach ($values as $unsigned) {

            /* 'v' is unsigned; 's' would follow machine byte order. */
            $sample = $unsigned >= 32768 ? $unsigned - 65536 : $unsigned;

            if (abs($sample) > abs($peak)) $peak = $sample;
        }

        $peaks[] = round($peak / 32768, 4);
    }

    fclose($handle);

    return count($peaks) === $buckets ? $peaks : [];
}
