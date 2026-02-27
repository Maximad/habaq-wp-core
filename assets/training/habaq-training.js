(function () {
  'use strict';

  function initTrainingPlayer(container) {
    var configNode = container.querySelector('.habaq-training-config');
    var mountNode = container.querySelector('.habaq-training__app');
    var fallbackNode = container.querySelector('.habaq-training__fallback');

    if (!configNode || !mountNode) {
      return;
    }

    var config = {};
    try {
      config = JSON.parse(configNode.textContent || '{}');
    } catch (error) {
      mountNode.innerHTML = '<p class="habaq-training__message">تعذر قراءة إعدادات التدريب.</p>';
      return;
    }

    if (!Array.isArray(config.slides) || config.slides.length === 0) {
      mountNode.innerHTML = '<p class="habaq-training__message">لا توجد شرائح متاحة حالياً.</p>';
      return;
    }

    if (fallbackNode) {
      fallbackNode.hidden = true;
    }

    var state = {
      started: false,
      current: 0,
      playing: false,
      muted: false,
      startAttempted: false
    };

    var storageKey = 'habaqTraining:' + (config.slug || 'default');
    var savedIndex = parseInt(window.localStorage.getItem(storageKey), 10);
    if (!isNaN(savedIndex) && savedIndex >= 0 && savedIndex < config.slides.length) {
      state.current = savedIndex;
    }

    mountNode.innerHTML = [
      '<div class="habaq-training__shell">',
      '  <div class="habaq-training__start" data-start-screen="1">',
      '    <h2 class="habaq-training__heading"></h2>',
      '    <p class="habaq-training__hint">اضغط للبدء ثم تنقّل بين الشرائح.</p>',
      '    <button type="button" class="habaq-training__button habaq-training__button--primary" data-action="start">ابدأ التدريب</button>',
      '  </div>',
      '  <div class="habaq-training__player" data-player="1" hidden>',
      '    <div class="habaq-training__status">',
      '      <span class="habaq-training__progress" data-progress></span>',
      '      <span class="habaq-training__title" data-title></span>',
      '    </div>',
      '    <article class="habaq-training__slide" data-slide-panel tabindex="-1">',
      '      <div class="habaq-training__image-wrap" data-image-wrap hidden><img data-image alt="" /></div>',
      '      <div class="habaq-training__body" data-body></div>',
      '    </article>',
      '    <div class="habaq-training__controls">',
      '      <button type="button" class="habaq-training__button" data-action="prev" aria-label="السابق">السابق</button>',
      '      <button type="button" class="habaq-training__button" data-action="play" aria-label="تشغيل أو إيقاف">تشغيل</button>',
      '      <button type="button" class="habaq-training__button" data-action="mute" aria-label="كتم الصوت">كتم</button>',
      '      <button type="button" class="habaq-training__button" data-action="restart" aria-label="إعادة البدء">إعادة</button>',
      '      <button type="button" class="habaq-training__button" data-action="next" aria-label="التالي">التالي</button>',
      '    </div>',
      '    <div class="habaq-training__timeline-wrap">',
      '      <label class="habaq-training__timeline-label"><span class="habaq-training__sr-only">شريط التقدم</span><input data-seek type="range" min="0" max="100" value="0" step="1" /></label>',
      '    </div>',
      '    <audio data-audio preload="metadata"></audio>',
      '    <p class="habaq-training__message" data-message hidden></p>',
      '  </div>',
      '</div>'
    ].join('');

    var startScreen = mountNode.querySelector('[data-start-screen]');
    var player = mountNode.querySelector('[data-player]');
    var heading = mountNode.querySelector('.habaq-training__heading');
    var progress = mountNode.querySelector('[data-progress]');
    var title = mountNode.querySelector('[data-title]');
    var body = mountNode.querySelector('[data-body]');
    var imageWrap = mountNode.querySelector('[data-image-wrap]');
    var image = mountNode.querySelector('[data-image]');
    var seek = mountNode.querySelector('[data-seek]');
    var audio = mountNode.querySelector('[data-audio]');
    var message = mountNode.querySelector('[data-message]');
    var slidePanel = mountNode.querySelector('[data-slide-panel]');

    heading.textContent = (config.meta && config.meta.title) ? config.meta.title : 'التدريب التفاعلي';

    function showMessage(text) {
      if (!text) {
        message.hidden = true;
        message.textContent = '';
        return;
      }
      message.hidden = false;
      message.textContent = text;
    }

    function getAudioUrl(slide) {
      if (!slide || !config.audioMap) {
        return '';
      }
      var entry = config.audioMap[String(slide.audio_index)] || config.audioMap[slide.audio_index];
      return entry && entry.url ? entry.url : '';
    }

    function renderSlide(focusPanel) {
      var slide = config.slides[state.current];
      if (!slide) {
        return;
      }

      progress.textContent = (state.current + 1) + ' / ' + config.slides.length;
      title.textContent = slide.title || '';
      body.innerHTML = slide.body_html || '';

      if (slide.image_url) {
        image.src = slide.image_url;
        image.alt = slide.title || '';
        imageWrap.hidden = false;
      } else {
        image.removeAttribute('src');
        image.alt = '';
        imageWrap.hidden = true;
      }

      var audioUrl = getAudioUrl(slide);
      if (!audioUrl) {
        showMessage('لا يوجد ملف صوتي للشريحة الحالية.');
        console.warn('Habaq Training: missing audio for index', slide.audio_index);
        audio.removeAttribute('src');
        audio.load();
      } else {
        showMessage('');
        audio.src = audioUrl;
        audio.muted = state.muted;
        audio.load();
      }

      updateButtons();
      seek.value = 0;
      window.localStorage.setItem(storageKey, String(state.current));

      if (focusPanel) {
        slidePanel.focus();
      }
    }

    function updateButtons() {
      var prevBtn = mountNode.querySelector('[data-action="prev"]');
      var playBtn = mountNode.querySelector('[data-action="play"]');
      var muteBtn = mountNode.querySelector('[data-action="mute"]');
      var nextBtn = mountNode.querySelector('[data-action="next"]');

      prevBtn.disabled = state.current === 0;
      nextBtn.disabled = state.current >= config.slides.length - 1;
      playBtn.textContent = state.playing ? 'إيقاف' : 'تشغيل';
      muteBtn.textContent = state.muted ? 'إلغاء الكتم' : 'كتم';
    }

    function playCurrent() {
      if (!audio.src) {
        return;
      }

      audio.play().then(function () {
        state.playing = true;
        updateButtons();
      }).catch(function () {
        state.playing = false;
        updateButtons();
      });
    }

    function pauseCurrent() {
      audio.pause();
      state.playing = false;
      updateButtons();
    }

    function changeSlide(nextIndex, focusPanel) {
      if (nextIndex < 0 || nextIndex >= config.slides.length) {
        return;
      }
      pauseCurrent();
      state.current = nextIndex;
      renderSlide(focusPanel);
      if (state.started) {
        playCurrent();
      }
    }

    mountNode.addEventListener('click', function (event) {
      var button = event.target.closest('[data-action]');
      if (!button) {
        return;
      }

      var action = button.getAttribute('data-action');

      if (action === 'start') {
        state.started = true;
        state.startAttempted = true;
        startScreen.hidden = true;
        player.hidden = false;
        renderSlide(true);
        playCurrent();
        return;
      }

      if (!state.started) {
        return;
      }

      if (action === 'prev') {
        changeSlide(state.current - 1, true);
      } else if (action === 'next') {
        changeSlide(state.current + 1, true);
      } else if (action === 'play') {
        if (state.playing) {
          pauseCurrent();
        } else {
          playCurrent();
        }
      } else if (action === 'mute') {
        state.muted = !state.muted;
        audio.muted = state.muted;
        updateButtons();
      } else if (action === 'restart') {
        changeSlide(0, true);
      }
    });

    mountNode.addEventListener('keydown', function (event) {
      if (!state.started) {
        return;
      }

      if (event.key === 'ArrowRight') {
        event.preventDefault();
        changeSlide(state.current + 1, true);
      } else if (event.key === 'ArrowLeft') {
        event.preventDefault();
        changeSlide(state.current - 1, true);
      }
    });

    audio.addEventListener('play', function () {
      state.playing = true;
      updateButtons();
    });

    audio.addEventListener('pause', function () {
      state.playing = false;
      updateButtons();
    });

    audio.addEventListener('timeupdate', function () {
      if (!audio.duration || !isFinite(audio.duration)) {
        seek.value = 0;
        return;
      }
      seek.value = String(Math.round((audio.currentTime / audio.duration) * 100));
    });

    seek.addEventListener('input', function () {
      if (!audio.duration || !isFinite(audio.duration)) {
        return;
      }
      audio.currentTime = (parseInt(seek.value, 10) / 100) * audio.duration;
    });

    audio.addEventListener('ended', function () {
      state.playing = false;
      updateButtons();

      var autoAdvance = !!(config.meta && config.meta.autoadvance);
      if (autoAdvance && state.current < config.slides.length - 1) {
        changeSlide(state.current + 1, true);
      }
    });

    if (Array.isArray(config.messages) && config.messages.length > 0) {
      showMessage(config.messages.join(' | '));
      config.messages.forEach(function (msg) {
        console.warn('Habaq Training:', msg);
      });
    }

    var touchStartX = null;
    mountNode.addEventListener('touchstart', function (event) {
      if (!state.started || !event.touches || event.touches.length === 0) {
        return;
      }
      touchStartX = event.touches[0].clientX;
    }, { passive: true });

    mountNode.addEventListener('touchend', function (event) {
      if (!state.started || touchStartX === null || !event.changedTouches || event.changedTouches.length === 0) {
        return;
      }

      var deltaX = event.changedTouches[0].clientX - touchStartX;
      touchStartX = null;

      if (Math.abs(deltaX) < 40) {
        return;
      }

      if (deltaX < 0) {
        changeSlide(state.current + 1, true);
      } else {
        changeSlide(state.current - 1, true);
      }
    }, { passive: true });

    updateButtons();
  }

  document.querySelectorAll('.habaq-training[data-habaq-training="1"]').forEach(initTrainingPlayer);
})();
