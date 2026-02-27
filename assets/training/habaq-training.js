(function () {
  'use strict';

  function parseConfig(node) {
    try {
      return JSON.parse(node.textContent || '{}');
    } catch (error) {
      return null;
    }
  }

  function nowTs() {
    return Math.floor(Date.now() / 1000);
  }

  function initTrainingPlayer(container) {
    var configNode = container.querySelector('.habaq-training-config');
    var mountNode = container.querySelector('.habaq-training__app');
    var fallbackNode = container.querySelector('.habaq-training__fallback');

    if (!configNode || !mountNode) {
      return;
    }

    var config = parseConfig(configNode);
    if (!config) {
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

    var meta = config.meta || {};
    var viewer = config.viewer || {};
    var loginUrl = config.login_url || '/wp-login.php';
    var previewSlides = parseInt(meta.preview_slides || 0, 10);
    if (isNaN(previewSlides) || previewSlides < 0) {
      previewSlides = 0;
    }

    var isLoggedIn = !!viewer.is_logged_in;
    var canTrackServer = !!viewer.can_track_server;

    var bootstrap = window.habaqTraining || {};
    var ajaxUrl = bootstrap.ajaxUrl || '';
    var nonce = bootstrap.nonce || '';

    var state = {
      started: false,
      current: 0,
      playing: false,
      muted: false,
      completed: !!(config.resume && config.resume.completed),
      completedAt: (config.resume && config.resume.completed_at) ? parseInt(config.resume.completed_at, 10) : 0,
      lastServerSaveAt: 0,
      promptVisible: false
    };

    var storageKey = 'habaq_training_progress:' + (config.slug || 'default');
    var localSaved = null;
    try {
      localSaved = JSON.parse(window.localStorage.getItem(storageKey) || 'null');
    } catch (e) {
      localSaved = null;
    }

    var serverSlide = (config.resume && typeof config.resume.current_slide !== 'undefined') ? parseInt(config.resume.current_slide, 10) : 0;
    if (isNaN(serverSlide) || serverSlide < 0) {
      serverSlide = 0;
    }

    var localSlide = (localSaved && typeof localSaved.current_slide !== 'undefined') ? parseInt(localSaved.current_slide, 10) : 0;
    if (isNaN(localSlide) || localSlide < 0) {
      localSlide = 0;
    }

    var suggestedResumeSlide = isLoggedIn ? serverSlide : localSlide;
    if (suggestedResumeSlide >= config.slides.length) {
      suggestedResumeSlide = config.slides.length - 1;
    }

    mountNode.innerHTML = [
      '<div class="habaq-training__shell">',
      '  <div class="habaq-training__start" data-start-screen="1">',
      '    <h2 class="habaq-training__heading"></h2>',
      '    <p class="habaq-training__hint">اضغط للبدء ثم تنقّل بين الشرائح.</p>',
      '    <div class="habaq-training__resume" data-resume hidden>',
      '      <p class="habaq-training__hint">متابعة من حيث توقفت؟</p>',
      '      <div class="habaq-training__resume-actions">',
      '        <button type="button" class="habaq-training__button" data-action="resume-continue">متابعة</button>',
      '        <button type="button" class="habaq-training__button" data-action="resume-restart">بدء من البداية</button>',
      '      </div>',
      '    </div>',
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
      '      <div class="habaq-training__preview-gate" data-preview-gate hidden>',
      '        <p class="habaq-training__gate-message">هذا التدريب متاح لأعضاء الفريق فقط.</p>',
      '        <a class="habaq-training__button habaq-training__button--primary" data-login-link href="#">تسجيل الدخول</a>',
      '      </div>',
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
      '    <div class="habaq-training__complete" data-complete-wrap hidden>',
      '      <label class="habaq-training__ack"><input type="checkbox" data-ack /> أُقِرّ بأنني اطّلعت على هذا التدريب وأفهم التزاماتي.</label>',
      '      <button type="button" class="habaq-training__button habaq-training__button--primary" data-action="finish">إنهاء التدريب</button>',
      '      <p class="habaq-training__badge" data-complete-badge hidden>مكتمل ✓</p>',
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
    var previewGate = mountNode.querySelector('[data-preview-gate]');
    var loginLink = mountNode.querySelector('[data-login-link]');
    var completeWrap = mountNode.querySelector('[data-complete-wrap]');
    var ack = mountNode.querySelector('[data-ack]');
    var completeBadge = mountNode.querySelector('[data-complete-badge]');
    var resumeBox = mountNode.querySelector('[data-resume]');

    if (loginLink) {
      loginLink.href = loginUrl;
    }

    heading.textContent = meta.title || 'التدريب التفاعلي';

    if (suggestedResumeSlide > 0) {
      state.promptVisible = true;
      resumeBox.hidden = false;
    }

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

    function inPreviewGate() {
      return !isLoggedIn && previewSlides > 0 && state.current >= previewSlides;
    }

    function saveLocalProgress() {
      var payload = {
        current_slide: state.current,
        updated_at: nowTs(),
        completed: !!state.completed
      };
      window.localStorage.setItem(storageKey, JSON.stringify(payload));
    }

    function saveServerProgress() {
      if (!isLoggedIn || !canTrackServer || !ajaxUrl || !nonce) {
        return;
      }

      var now = Date.now();
      if ((now - state.lastServerSaveAt) < 3000) {
        return;
      }
      state.lastServerSaveAt = now;

      var bodyData = new URLSearchParams();
      bodyData.set('action', 'habaq_training_save_progress');
      bodyData.set('nonce', nonce);
      bodyData.set('slug', config.slug || 'default');
      bodyData.set('version', meta.version || '1');
      bodyData.set('current_slide', String(state.current));
      bodyData.set('completed', state.completed ? '1' : '0');

      fetch(ajaxUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: bodyData.toString(),
        credentials: 'same-origin'
      }).catch(function () {
        // no-op
      });
    }

    function renderCompletionState() {
      if (state.completed) {
        completeBadge.hidden = false;
      }

      var mustAck = !!meta.require_ack;
      if (!mustAck) {
        ack.checked = true;
      }

      var finishButton = mountNode.querySelector('[data-action="finish"]');
      if (!finishButton) {
        return;
      }

      finishButton.disabled = mustAck && !ack.checked;
    }

    function setPreviewGateUI(enabled) {
      previewGate.hidden = !enabled;
      body.hidden = enabled;
      imageWrap.hidden = true;
      seek.disabled = enabled;
      audio.pause();
      if (enabled) {
        audio.removeAttribute('src');
        audio.load();
        showMessage('');
      }
    }

    function renderSlide(focusPanel) {
      var slide = config.slides[state.current];
      if (!slide) {
        return;
      }

      progress.textContent = (state.current + 1) + ' / ' + config.slides.length;
      title.textContent = slide.title || '';

      if (inPreviewGate()) {
        setPreviewGateUI(true);
        updateButtons();
        if (focusPanel) {
          slidePanel.focus();
        }
        return;
      }

      setPreviewGateUI(false);
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

      seek.value = 0;
      updateButtons();
      saveLocalProgress();
      saveServerProgress();

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

      if (inPreviewGate()) {
        playBtn.disabled = true;
        muteBtn.disabled = true;
      } else {
        playBtn.disabled = false;
        muteBtn.disabled = false;
      }

      completeWrap.hidden = state.current !== (config.slides.length - 1) || inPreviewGate();
      renderCompletionState();
    }

    function playCurrent() {
      if (inPreviewGate() || !audio.src) {
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

    function markCompleted() {
      state.completed = true;
      state.completedAt = nowTs();
      saveLocalProgress();

      if (!isLoggedIn || !canTrackServer || !ajaxUrl || !nonce) {
        renderCompletionState();
        return;
      }

      var bodyData = new URLSearchParams();
      bodyData.set('action', 'habaq_training_mark_complete');
      bodyData.set('nonce', nonce);
      bodyData.set('slug', config.slug || 'default');
      bodyData.set('version', meta.version || '1');
      bodyData.set('current_slide', String(state.current));

      fetch(ajaxUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: bodyData.toString(),
        credentials: 'same-origin'
      }).then(function () {
        renderCompletionState();
      }).catch(function () {
        renderCompletionState();
      });
    }

    mountNode.addEventListener('click', function (event) {
      var button = event.target.closest('[data-action]');
      if (!button) {
        return;
      }

      var action = button.getAttribute('data-action');

      if (action === 'resume-continue') {
        state.current = suggestedResumeSlide;
        state.promptVisible = false;
        resumeBox.hidden = true;
        return;
      }

      if (action === 'resume-restart') {
        state.current = 0;
        state.promptVisible = false;
        resumeBox.hidden = true;
        return;
      }

      if (action === 'start') {
        if (state.promptVisible) {
          state.current = suggestedResumeSlide;
          state.promptVisible = false;
          resumeBox.hidden = true;
        }

        state.started = true;
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
      } else if (action === 'finish') {
        var mustAck = !!meta.require_ack;
        if (mustAck && !ack.checked) {
          showMessage('يرجى الإقرار قبل إنهاء التدريب.');
          return;
        }
        showMessage('');
        markCompleted();
      }
    });

    ack.addEventListener('change', function () {
      renderCompletionState();
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

      var autoAdvance = !!meta.autoadvance;
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
