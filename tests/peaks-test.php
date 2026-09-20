<?php
/**
 * Self-check for the PCM bucketing in includes/peaks.php.
 *
 *   php tests/peaks-test.php
 *
 * Runs standalone: no WordPress, no framework. Only the pure function is
 * pulled in, since everything else in peaks.php needs WP loaded.
 */

define('ABSPATH', __DIR__);

/* Grab just elin_audio_extract_peaks(), skipping the WP-dependent rest. */
$source = file_get_contents(__DIR__ . '/../includes/peaks.php');
$start = strpos($source, 'function elin_audio_extract_peaks');
eval(substr($source, $start));

$tmp = tempnam(sys_get_temp_dir(), 'peaks');
$failures = 0;

function check($label, $condition, $actual = null)
{
    global $failures;

    if ($condition) {
        echo "  ok    $label\n";
        return;
    }

    $failures++;
    echo "  FAIL  $label" . (null === $actual ? '' : ' — got ' . var_export($actual, true)) . "\n";
}

function write_samples($file, array $samples)
{
    $raw = '';

    foreach ($samples as $s) {
        $raw .= pack('v', $s < 0 ? $s + 65536 : $s);
    }

    file_put_contents($file, $raw);
}

echo "elin_audio_extract_peaks()\n";

/* A ramp: each bucket's loudest sample is its last one. */
$samples = [];
for ($i = 0; $i < 1000; $i++) $samples[] = (int) round($i * 32.767);
write_samples($tmp, $samples);

$peaks = elin_audio_extract_peaks($tmp, 10);

check('ramp returns one value per bucket', count($peaks) === 10, count($peaks));
check('ramp rises monotonically', $peaks === array_values(array_unique($peaks)) && $peaks[0] < $peaks[9]);
check('ramp stays inside -1..1', max($peaks) <= 1 && min($peaks) >= -1, [min($peaks), max($peaks)]);

/* Sign must survive: a loud negative bucket may not come back positive. */
write_samples($tmp, array_merge(array_fill(0, 500, -30000), array_fill(0, 500, 1000)));
$peaks = elin_audio_extract_peaks($tmp, 2);

check('keeps the sign of the loudest sample', $peaks[0] < 0, $peaks[0]);
check('negative peak is normalised', abs($peaks[0] + 0.9155) < 0.001, $peaks[0]);
check('quiet bucket stays quiet', abs($peaks[1] - 0.0305) < 0.001, $peaks[1]);

/* Full-scale negative is the edge of the unsigned -> signed conversion. */
write_samples($tmp, array_fill(0, 100, -32768));
$peaks = elin_audio_extract_peaks($tmp, 1);

check('-32768 maps to -1.0', $peaks === [-1.0], $peaks);

/* Refuse rather than guess when there is not enough audio to fill the bars. */
write_samples($tmp, array_fill(0, 5, 1000));

check('too few samples returns empty', elin_audio_extract_peaks($tmp, 100) === []);
check('missing file returns empty', elin_audio_extract_peaks($tmp . '.nope', 10) === []);

/* An odd trailing byte must not make unpack() read past the buffer. */
write_samples($tmp, array_fill(0, 200, 5000));
file_put_contents($tmp, "\x01", FILE_APPEND);

check('odd trailing byte is ignored', count(elin_audio_extract_peaks($tmp, 10)) === 10);

unlink($tmp);

echo $failures ? "\n$failures failed\n" : "\nall passed\n";

exit($failures ? 1 : 0);
