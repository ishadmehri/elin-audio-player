<?php

if (! defined('ABSPATH')) exit;

class ElinAudioPlayerWidget extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'elin_audio_player';
    }

    public function get_title()
    {
        return __('Elin Audio Player', 'elin-audio-player');
    }

    public function get_icon()
    {
        return 'eicon-play';
    }

    public function get_categories()
    {
        return ['general'];
    }

    public function get_keywords()
    {
        return ['audio', 'podcast', 'player', 'wave', 'صوت', 'پادکست', 'پلیر'];
    }

    public function get_style_depends()
    {
        return ['elin-audio-style'];
    }

    public function get_script_depends()
    {
        return ['wavesurfer', 'elin-audio-script'];
    }

    protected function register_controls()
    {

        /* =========================
           CONTENT
        ========================= */

        $this->start_controls_section(
            'content_section',
            [
                'label' => __('تنظیمات پلیر', 'elin-audio-player'),
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => __('عنوان', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('عنوان پادکست', 'elin-audio-player'),
            ]
        );

        $this->add_control(
            'description',
            [
                'label' => __('توضیحات', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('توضیح کوتاه', 'elin-audio-player'),
            ]
        );

        $this->add_control(
            'duration_label',
            [
                'label' => __('برچسب مدت زمان', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('زمان مورد نیاز:', 'elin-audio-player'),
            ]
        );

        $this->add_control(
            'audio_file',
            [
                'label' => __('فایل صوتی', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'media_types' => ['audio'],
            ]
        );

        $this->add_control(
            'skip_seconds',
            [
                'label' => __('مقدار جلو/عقب رفتن (ثانیه)', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 15,
                'min' => 1,
                'max' => 120,
            ]
        );

        $this->end_controls_section();

        /* =========================
           STYLE — BOX
        ========================= */

        $this->start_controls_section(
            'style_box_section',
            [
                'label' => __('جعبه پلیر', 'elin-audio-player'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'box_background',
            [
                'label' => __('رنگ پس‌زمینه', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#FCFCFC',
                'selectors' => [
                    '{{WRAPPER}} .elin-player' => '--elin-bg: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'box_border_color',
            [
                'label' => __('رنگ حاشیه', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#D0D5DD',
                'selectors' => [
                    '{{WRAPPER}} .elin-player' => '--elin-border: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'box_radius',
            [
                'label' => __('گردی گوشه‌ها', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 0, 'max' => 48]],
                'selectors' => [
                    '{{WRAPPER}} .elin-player' => '--elin-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'box_padding',
            [
                'label' => __('فاصله داخلی', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .elin-player' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* =========================
           STYLE — TEXT
        ========================= */

        $this->start_controls_section(
            'style_text_section',
            [
                'label' => __('متن', 'elin-audio-player'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('رنگ عنوان', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#111827',
                'selectors' => [
                    '{{WRAPPER}} .elin-player' => '--elin-title-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .elin-player-title',
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('رنگ متن‌های فرعی', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#6B7280',
                'selectors' => [
                    '{{WRAPPER}} .elin-player' => '--elin-text-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .elin-player-description',
            ]
        );

        $this->end_controls_section();

        /* =========================
           STYLE — WAVE & CONTROLS
        ========================= */

        $this->start_controls_section(
            'style_wave_section',
            [
                'label' => __('موج صوتی و کنترل‌ها', 'elin-audio-player'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'wave_color',
            [
                'label' => __('رنگ موج', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#C5CAD3',
            ]
        );

        $this->add_control(
            'progress_color',
            [
                'label' => __('رنگ موج پخش‌شده', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#6BB29D',
            ]
        );

        $this->add_control(
            'progress_color_mobile',
            [
                'label' => __('رنگ موج پخش‌شده (موبایل)', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#667085',
            ]
        );

        $this->add_control(
            'accent_color',
            [
                'label' => __('رنگ اصلی (سرعت پخش)', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#61B09B',
                'selectors' => [
                    '{{WRAPPER}} .elin-player' => '--elin-accent: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => __('رنگ آیکون‌ها', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#344054',
                'selectors' => [
                    '{{WRAPPER}} .elin-player' => '--elin-icon-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'play_icon_color',
            [
                'label' => __('رنگ دکمه پخش', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#101828',
                'selectors' => [
                    '{{WRAPPER}} .elin-player' => '--elin-play-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'controls_background',
            [
                'label' => __('پس‌زمینه نوار کنترل', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#F9FAFB',
                'selectors' => [
                    '{{WRAPPER}} .elin-player' => '--elin-controls-bg: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'controls_border_color',
            [
                'label' => __('حاشیه نوار کنترل', 'elin-audio-player'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#EAECF0',
                'selectors' => [
                    '{{WRAPPER}} .elin-player' => '--elin-controls-border: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Skip icon, mirrored for the forward button.
     */
    private function skip_icon($direction)
    {

        if ('forward' === $direction) {
            $arc = 'M2.50781 4.81304V11.193C2.50781 12.4997 3.9278 13.3197 5.06114 12.6664L7.8278 11.073L10.5945 9.47304C11.7278 8.8197 11.7278 7.18637 10.5945 6.53303L7.8278 4.93304L5.06114 3.33971C3.9278 2.68638 2.50781 3.49971 2.50781 4.81304Z';
            $bar = 'M13.4961 12.1196V3.87964';
        } else {
            $arc = 'M13.4953 4.81304V11.193C13.4953 12.4997 12.0753 13.3197 10.9419 12.6664L8.17528 11.073L5.40859 9.47304C4.27526 8.8197 4.27526 7.18637 5.40859 6.53303L8.17528 4.93304L10.9419 3.33971C12.0753 2.68638 13.4953 3.49971 13.4953 4.81304Z';
            $bar = 'M2.50781 12.1196V3.87964';
        }

        return '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">'
            . '<path d="' . $arc . '" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />'
            . '<path d="' . $bar . '" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />'
            . '</svg>';
    }

    /**
     * Renders the steps of the playback speed slider.
     */
    private function speed_steps($current = 1)
    {

        $speeds = [0.5, 0.75, 1, 1.25, 1.5];

        $html = '';

        foreach ($speeds as $speed) {

            $html .= sprintf(
                '<button type="button" class="speed-step%1$s" data-speed="%2$s" aria-label="%3$s"></button>',
                ((float) $speed === (float) $current ? ' active' : ''),
                esc_attr($speed),
                esc_attr(sprintf(
                    /* translators: %s: playback rate, e.g. 1.25 */
                    __('سرعت پخش %sx', 'elin-audio-player'),
                    $speed
                ))
            );
        }

        return $html;
    }

    protected function render()
    {

        $settings = $this->get_settings_for_display();

        $audio = isset($settings['audio_file']['url']) ? $settings['audio_file']['url'] : '';

        if ('' === $audio) {

            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                printf(
                    '<div class="elin-player elin-player-empty">%s</div>',
                    esc_html__('برای نمایش پلیر، یک فایل صوتی انتخاب کنید.', 'elin-audio-player')
                );
            }

            return;
        }

        $skip = ! empty($settings['skip_seconds']) ? absint($settings['skip_seconds']) : 15;

        if (0 === $skip) $skip = 15;

        $this->add_render_attribute('player', [
            'class' => 'elin-player',
            'id' => 'elin-player-' . $this->get_id(),
            'data-audio' => esc_url($audio),
            'data-skip' => $skip,
            'data-wave-color' => ! empty($settings['wave_color']) ? $settings['wave_color'] : '#c5cad3',
            'data-progress-color' => ! empty($settings['progress_color']) ? $settings['progress_color'] : '#6bb29d',
            'data-progress-color-mobile' => ! empty($settings['progress_color_mobile']) ? $settings['progress_color_mobile'] : '#667085',
        ]);

        /* translators: %s: number of seconds to skip */
        $skip_label = sprintf(__('%s ثانیه', 'elin-audio-player'), $skip);

?>

        <div <?php $this->print_render_attribute_string('player'); ?>>

            <div class="elin-top">

                <div class="elin-player-content">

                    <?php if (! empty($settings['title'])) : ?>
                        <h2 class="elin-player-title">
                            <?php echo esc_html($settings['title']); ?>
                        </h2>
                    <?php endif; ?>

                    <?php if (! empty($settings['description'])) : ?>
                        <p class="elin-player-description">
                            <?php echo esc_html($settings['description']); ?>
                        </p>
                    <?php endif; ?>

                    <?php if (! empty($settings['duration_label'])) : ?>
                        <span class="elin-duration-label">
                            <?php echo esc_html($settings['duration_label']); ?>
                            <span class="total-time">00:00</span>
                        </span>
                    <?php endif; ?>

                </div>

            </div>

            <div class="elin-time">

                <span class="current-time">00:00</span>
                <div class="waveform"></div>
                <span class="total-time">00:00</span>

            </div>

            <div class="elin-controls">

                <div class="play-wrap">

                    <button type="button" class="backward" aria-label="<?php echo esc_attr(sprintf(__('%s ثانیه به عقب', 'elin-audio-player'), $skip)); ?>">
                        <span class="skip-label"><?php echo esc_html($skip_label); ?></span>
                        <?php echo $this->skip_icon('backward'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </button>

                    <button type="button" class="play" aria-label="<?php esc_attr_e('پخش', 'elin-audio-player'); ?>" data-pause-label="<?php esc_attr_e('توقف', 'elin-audio-player'); ?>" aria-pressed="false">
                        <svg class="icon-play" width="29" height="30" viewBox="0 0 29 30" fill="none" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0.75 14.9552V9.02189C0.75 1.65523 5.96667 -1.36144 12.35 2.3219L17.5 5.28856L22.65 8.25523C29.0333 11.9386 29.0333 17.9719 22.65 21.6552L17.5 24.6219L12.35 27.5886C5.96667 31.2719 0.75 28.2552 0.75 20.8886V14.9552Z" fill="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <svg class="icon-pause" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.65 19.11V4.89C10.65 3.54 10.08 3 8.64 3H5.01C3.57 3 3 3.54 3 4.89V19.11C3 20.46 3.57 21 5.01 21H8.64C10.08 21 10.65 20.46 10.65 19.11Z" />
                            <path d="M21.0001 19.11V4.89C21.0001 3.54 20.4301 3 18.9901 3H15.3601C13.9301 3 13.3501 3.54 13.3501 4.89V19.11C13.3501 20.46 13.9201 21 15.3601 21H18.9901C20.4301 21 21.0001 20.46 21.0001 19.11Z" />
                        </svg>
                    </button>

                    <button type="button" class="forward" aria-label="<?php echo esc_attr(sprintf(__('%s ثانیه به جلو', 'elin-audio-player'), $skip)); ?>">
                        <?php echo $this->skip_icon('forward'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <span class="skip-label"><?php echo esc_html($skip_label); ?></span>
                    </button>

                </div>

                <div class="speed-wrap">

                    <div class="speed-controller">

                        <div class="speed-slider">

                            <div class="speed-line"></div>

                            <?php echo $this->speed_steps(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

                            <div class="speed-knob">
                                <span class="speed-value">1x</span>
                            </div>

                        </div>

                    </div>

                    <!-- MOBILE -->

                    <div class="mobile-speed">

                        <button type="button" class="mobile-speed-btn" aria-label="<?php esc_attr_e('تغییر سرعت پخش', 'elin-audio-player'); ?>" aria-expanded="false">
                            <span class="mobile-speed-value">1x</span>
                        </button>

                        <div class="mobile-popup" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('سرعت پخش', 'elin-audio-player'); ?>">

                            <div class="mobile-popup-content">

                                <div class="mobile-speed-slider">

                                    <div class="speed-line"></div>

                                    <?php echo $this->speed_steps(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

                                    <div class="speed-knob">
                                        <span class="speed-value">1x</span>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <span class="speed-label"><?php esc_html_e('سرعت پخش :', 'elin-audio-player'); ?></span>

                </div>

            </div>

        </div>

<?php
    }
}
