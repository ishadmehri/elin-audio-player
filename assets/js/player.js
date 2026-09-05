(function ($) {
  "use strict";

  var MOBILE_BREAKPOINT = 768;
  var SPEEDS = [0.5, 0.75, 1, 1.25, 1.5];

  function isMobile() {
    return window.innerWidth < MOBILE_BREAKPOINT;
  }

  /* Wave rendering options depend on the viewport, so they are recomputed
     on resize instead of being frozen at load time. */
  function waveOptions(player) {
    var mobile = isMobile();

    return {
      waveColor: player.data("wave-color") || "#c5cad3",

      progressColor: mobile
        ? player.data("progress-color-mobile") || "#667085"
        : player.data("progress-color") || "#6bb29d",

      height: mobile ? 16 : 32,

      barWidth: mobile ? 2 : 3,

      barGap: mobile ? 2 : 3,

      barRadius: mobile ? 2 : 3,
    };
  }

  function formatTime(seconds) {
    if (!isFinite(seconds) || seconds < 0) seconds = 0;

    var hrs = Math.floor(seconds / 3600);
    var mins = Math.floor((seconds % 3600) / 60);
    var secs = Math.floor(seconds % 60);

    var pad = function (n) {
      return n < 10 ? "0" + n : String(n);
    };

    return (hrs > 0 ? hrs + ":" + pad(mins) : pad(mins)) + ":" + pad(secs);
  }

  function initPlayer(el) {
    var player = $(el);

    /* Elementor re-renders widgets in the editor; never bind twice. */
    if (player.data("elinInitialised")) return;

    var audio = player.data("audio");
    var waveform = player.find(".waveform")[0];

    if (!audio || !waveform || typeof WaveSurfer === "undefined") return;

    player.data("elinInitialised", true);

    var wavesurfer = WaveSurfer.create(
      $.extend(
        {
          container: waveform,
          url: audio,
          cursorWidth: 0,
        },
        waveOptions(player)
      )
    );

    var skip = parseInt(player.data("skip"), 10);
    if (!skip || skip < 1) skip = 15;

    /* =========================
       PLAY / PAUSE
    ========================= */

    var playBtn = player.find(".play");
    var playLabel = playBtn.attr("aria-label") || "";
    var pauseLabel = playBtn.data("pause-label") || "توقف";

    playBtn.on("click", function () {
      wavesurfer.playPause();
    });

    function syncPlayState(playing) {
      player.toggleClass("is-playing", playing);

      playBtn
        .attr("aria-pressed", playing ? "true" : "false")
        .attr("aria-label", playing ? pauseLabel : playLabel);
    }

    wavesurfer.on("play", function () {
      syncPlayState(true);
    });

    wavesurfer.on("pause", function () {
      syncPlayState(false);
    });

    wavesurfer.on("finish", function () {
      syncPlayState(false);
    });

    /* =========================
       SKIP
    ========================= */

    function seekBy(offset) {
      var duration = wavesurfer.getDuration() || 0;
      var target = wavesurfer.getCurrentTime() + offset;

      /* Clamp, otherwise a negative or past-the-end time is passed through. */
      target = Math.max(0, Math.min(target, duration));

      wavesurfer.setTime(target);
    }

    player.find(".forward").on("click", function () {
      seekBy(skip);
    });

    player.find(".backward").on("click", function () {
      seekBy(-skip);
    });

    /* =========================
       SPEED — DESKTOP + MOBILE
    ========================= */

    var currentSpeed = 1;

    var slider = player.find(".speed-slider");
    var knob = slider.find(".speed-knob");
    var steps = slider.find(".speed-step");

    var mobilePopup = player.find(".mobile-popup");
    var mobileBtn = player.find(".mobile-speed-btn");

    var mobileSlider = player.find(".mobile-speed-slider");
    var mobileKnob = mobileSlider.find(".speed-knob");
    var mobileSteps = mobileSlider.find(".speed-step");

    function moveKnob(sliderEl, knobEl, stepsEl, speed) {
      var index = SPEEDS.indexOf(speed);

      if (index < 0 || !sliderEl.length || !stepsEl.length) return;

      var step = stepsEl.eq(index);

      var sliderRect = sliderEl[0].getBoundingClientRect();

      /* A hidden slider has no width; skip and recompute once it is shown. */
      if (!sliderRect.width) return;

      var stepRect = step[0].getBoundingClientRect();

      var left = stepRect.left - sliderRect.left + stepRect.width / 2;

      knobEl.css({
        left: left + "px",
        transform: "translate(-50%, -50%)",
      });
    }

    function setSpeed(speed) {
      currentSpeed = speed;

      wavesurfer.setPlaybackRate(speed);

      steps.removeClass("active").filter('[data-speed="' + speed + '"]').addClass("active");
      mobileSteps.removeClass("active").filter('[data-speed="' + speed + '"]').addClass("active");

      slider.find(".speed-value").text(speed + "x");
      player.find(".mobile-speed-value").text(speed + "x");
      mobileKnob.find(".speed-value").text(speed + "x");

      moveKnob(slider, knob, steps, speed);
      moveKnob(mobileSlider, mobileKnob, mobileSteps, speed);
    }

    steps.on("click", function () {
      setSpeed(parseFloat($(this).data("speed")));
    });

    mobileSteps.on("click", function () {
      setSpeed(parseFloat($(this).data("speed")));
    });

    function openPopup() {
      mobilePopup.addClass("show");
      mobileBtn.attr("aria-expanded", "true");

      /* The slider has a width only once the popup is visible. */
      window.requestAnimationFrame(function () {
        moveKnob(mobileSlider, mobileKnob, mobileSteps, currentSpeed);
      });
    }

    function closePopup() {
      mobilePopup.removeClass("show");
      mobileBtn.attr("aria-expanded", "false");
    }

    mobileBtn.on("click", openPopup);

    mobilePopup.on("click", function (e) {
      if ($(e.target).hasClass("mobile-popup")) closePopup();
    });

    $(document).on("keydown.elinAudio", function (e) {
      if (e.key === "Escape" && mobilePopup.hasClass("show")) closePopup();
    });

    /* =========================
       TIME READOUT
    ========================= */

    wavesurfer.on("ready", function () {
      player.find(".total-time").text(formatTime(wavesurfer.getDuration()));

      /* Layout is settled by now, so the knob lands on the right step. */
      setSpeed(currentSpeed);
    });

    wavesurfer.on("timeupdate", function () {
      player.find(".current-time").text(formatTime(wavesurfer.getCurrentTime()));
    });

    wavesurfer.on("error", function () {
      player.addClass("elin-player-error");
    });

    /* =========================
       RESIZE
    ========================= */

    var wasMobile = isMobile();
    var resizeTimer;

    $(window).on("resize.elinAudio-" + (player.attr("id") || ""), function () {
      clearTimeout(resizeTimer);

      resizeTimer = setTimeout(function () {
        if (isMobile() !== wasMobile) {
          wasMobile = isMobile();
          wavesurfer.setOptions(waveOptions(player));
        }

        moveKnob(slider, knob, steps, currentSpeed);
        moveKnob(mobileSlider, mobileKnob, mobileSteps, currentSpeed);
      }, 150);
    });

    setSpeed(1);
  }

  function initAll(scope) {
    $(scope || document)
      .find(".elin-player")
      .addBack(".elin-player")
      .each(function () {
        initPlayer(this);
      });
  }

  $(document).ready(function () {
    initAll(document);
  });

  /* Elementor editor: re-init after a widget is re-rendered. */
  $(window).on("elementor/frontend/init", function () {
    if (!window.elementorFrontend || !elementorFrontend.hooks) return;

    elementorFrontend.hooks.addAction(
      "frontend/element_ready/elin_audio_player.default",
      function ($scope) {
        initAll($scope);
      }
    );
  });
})(jQuery);
