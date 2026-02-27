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

  function icon(name) {
    var icons = {
      play: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"></path></svg>',
      pause: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5h4v14H7zm6 0h4v14h-4z"></path></svg>',
      prev: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6h2v12H6zm3 6l9-6v12z"></path></svg>',
      next: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 6h2v12h-2zM7 18V6l9 6z"></path></svg>',
      mute: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 9v6h4l5 4V5L9 9zm10.5 3 2.5 2.5-1.5 1.5-2.5-2.5-2.5 2.5-1.5-1.5 2.5-2.5-2.5-2.5 1.5-1.5 2.5 2.5 2.5-2.5 1.5 1.5z"></path></svg>',
      unmute: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 9v6h4l5 4V5L9 9zm11.5 3a3.5 3.5 0 0 0-1.75-3.03v6.06A3.5 3.5 0 0 0 16.5 12zm0-7a10.5 10.5 0 0 1 0 14l-1.42-1.42a8.5 8.5 0 0 0 0-11.16z"></path></svg>',
      menu: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"></path></svg>',
      restart: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5V1L7 6l5 5V7a5 5 0 1 1-5 5H5a7 7 0 1 0 7-7z"></path></svg>'
    };

    return icons[name] || '';
  }

  function setButtonContent(button, iconName, label) {
    if (!button) {
      return;
    }

    button.innerHTML = '<span class="habaq-training__button-icon">' + icon(iconName) + '</span><span class="habaq-training__button-label">' + label + '</span>';
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

    var introIndex = 0;
    config.slides.forEach(function (slide, idx) {
      if (slide && Number(slide.audio_index) === 1 && introIndex === 0) {
        introIndex = idx;
      }
    });

    var state = {
      started: false,
      current: introIndex,
      playing: false,
      muted: false,
      completed: !!(config.resume && config.resume.completed),
      completedAt: (config.resume && config.resume.completed_at) ? parseInt(config.resume.completed_at, 10) : 0,
      lastServerSaveAt: 0,
      promptVisible: false,
      loading: false,
      sidebarOpen: false,
      preserveHero: false
    };

    var storageKey = 'habaq_training_progress:' + (config.slug || 'default');
    var sidebarStorageKey = 'habaq_training_sidebar:' + (config.slug || 'default');
    var localSaved = null;
    try {
      localSaved = JSON.parse(window.localStorage.getItem(storageKey) || 'null');
      state.sidebarOpen = window.localStorage.getItem(sidebarStorageKey) === '1';
    } catch (e) {
      localSaved = null;
      state.sidebarOpen = false;
    }

    var serverSlide = (config.resume && typeof config.resume.current_slide !== 'undefined') ? parseInt(config.resume.current_slide, 10) : introIndex;
    if (isNaN(serverSlide) || serverSlide < 0) {
      serverSlide = introIndex;
    }

    var localSlide = (localSaved && typeof localSaved.current_slide !== 'undefined') ? parseInt(localSaved.current_slide, 10) : introIndex;
    if (isNaN(localSlide) || localSlide < 0) {
      localSlide = introIndex;
    }

    var suggestedResumeSlide = isLoggedIn ? serverSlide : localSlide;
    if (suggestedResumeSlide >= config.slides.length) {
      suggestedResumeSlide = config.slides.length - 1;
    }

    mountNode.innerHTML = [
      '<div class="habaq-training__shell">',
      '  <aside class="habaq-training__sidebar" data-sidebar>',
      '    <div class="habaq-training__sidebar-header">الفهرس</div>',
      '    <ol class="habaq-training__toc" data-toc></ol>',
      '  </aside>',
      '  <button type="button" class="habaq-training__sidebar-overlay" data-sidebar-overlay hidden aria-label="إغلاق الفهرس"></button>',
      '  <div class="habaq-training__main">',
      '  <div class="habaq-training__start" data-start-screen="1">',
      '    <h2 class="habaq-training__heading"></h2>',
      '    <div class="habaq-training__image-wrap" data-start-image-wrap hidden><img class="habaq-training__image" data-start-image alt="" loading="eager" /><div class="habaq-training__image-placeholder" data-start-image-placeholder hidden></div></div>',
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
      '    <div class="habaq-training__topbar">',
      '      <button type="button" class="habaq-training__button" data-action="toggle-sidebar" aria-label="الفهرس"></button>',
      '      <div class="habaq-training__status">',
      '        <span class="habaq-training__progress" data-progress></span>',
      '        <span class="habaq-training__title" data-title></span>',
      '      </div>',
      '    </div>',
      '    <article class="habaq-training__slide" data-slide-panel tabindex="-1">',
      '      <div class="habaq-training__loading" data-loading hidden><span class="habaq-training__skeleton habaq-training__skeleton--media"></span><span class="habaq-training__skeleton habaq-training__skeleton--line"></span></div>',
      '      <div class="habaq-training__image-wrap" data-image-wrap hidden><img class="habaq-training__image" data-image alt="" loading="lazy" /><div class="habaq-training__image-placeholder" data-image-placeholder hidden></div></div>',
      '      <div class="habaq-training__body" data-body></div>',
      '      <div class="habaq-training__preview-gate" data-preview-gate hidden>',
      '        <p class="habaq-training__gate-message">هذا التدريب متاح لأعضاء الفريق فقط.</p>',
      '        <a class="habaq-training__button habaq-training__button--primary" data-login-link href="#">تسجيل الدخول</a>',
      '      </div>',
      '    </article>',
      '    <div class="habaq-training__controls">',
      '      <button type="button" class="habaq-training__button" data-action="prev" aria-label="السابق"></button>',
      '      <button type="button" class="habaq-training__button" data-action="play" aria-label="تشغيل أو إيقاف"></button>',
      '      <button type="button" class="habaq-training__button" data-action="mute" aria-label="كتم الصوت"></button>',
      '      <button type="button" class="habaq-training__button" data-action="restart" aria-label="إعادة البدء"></button>',
      '      <button type="button" class="habaq-training__button" data-action="next" aria-label="التالي"></button>',
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
    var imagePlaceholder = mountNode.querySelector('[data-image-placeholder]');
    var startImageWrap = mountNode.querySelector('[data-start-image-wrap]');
    var startImage = mountNode.querySelector('[data-start-image]');
    var startImagePlaceholder = mountNode.querySelector('[data-start-image-placeholder]');
    var loadingEl = mountNode.querySelector('[data-loading]');
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
    var sidebar = mountNode.querySelector('[data-sidebar]');
    var sidebarOverlay = mountNode.querySelector('[data-sidebar-overlay]');
    var toc = mountNode.querySelector('[data-toc]');

    var preloadLinks = {};

    function setLoading(enabled) {
      state.loading = enabled;
      loadingEl.hidden = !enabled;
    }

    function preloadImage(url) {
      if (!url) {
        return;
      }
      var probe = new Image();
      probe.src = url;
    }

    function preloadAudio(url) {
      if (!url || preloadLinks[url]) {
        return;
      }
      var link = document.createElement('link');
      link.rel = 'preload';
      link.as = 'audio';
      link.href = url;
      document.head.appendChild(link);
      preloadLinks[url] = link;
    }

    function getAudioUrl(slide) {
      if (!slide || !config.audioMap) {
        return '';
      }
      var entry = config.audioMap[String(slide.audio_index)] || config.audioMap[slide.audio_index];
      return entry && entry.url ? entry.url : '';
    }

    function preloadNeighbors(index) {
      var currentSlide = config.slides[index];
      var nextSlide = config.slides[index + 1];
      var prevSlide = config.slides[index - 1];

      if (currentSlide && currentSlide.image_url) {
        preloadImage(currentSlide.image_url);
      }
      if (nextSlide && nextSlide.image_url) {
        preloadImage(nextSlide.image_url);
      }
      if (nextSlide) {
        preloadAudio(getAudioUrl(nextSlide));
      }
      if (prevSlide && prevSlide.image_url) {
        preloadImage(prevSlide.image_url);
      }
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
      }).catch(function () {});
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

    function setPreviewGateUI(enabled) {
      previewGate.hidden = !enabled;
      body.hidden = enabled;
      imageWrap.hidden = true;
      image.hidden = true;
      imagePlaceholder.hidden = false;
      seek.disabled = enabled;
      audio.pause();
      if (enabled) {
        audio.removeAttribute('src');
        audio.load();
        showMessage('');
      }
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

    function renderToc() {
      toc.innerHTML = '';
      config.slides.forEach(function (slide, idx) {
        var number = (typeof slide.audio_index !== 'undefined' && slide.audio_index !== null) ? slide.audio_index : (idx + 1);
        var label = slide.title || ('شريحة ' + (idx + 1));
        var active = idx === state.current ? ' is-active' : '';
        toc.insertAdjacentHTML('beforeend', '<li><button type="button" class="habaq-training__toc-item' + active + '" data-slide-index="' + idx + '"><span class="habaq-training__toc-num">' + number + '</span><span class="habaq-training__toc-label">' + label + '</span></button></li>');
      });
    }

    function setSidebar(open) {
      state.sidebarOpen = !!open;
      sidebar.classList.toggle('is-open', state.sidebarOpen);
      sidebarOverlay.hidden = !state.sidebarOpen;
      window.localStorage.setItem(sidebarStorageKey, state.sidebarOpen ? '1' : '0');
    }

    function renderSlide(focusPanel) {
      var slide = config.slides[state.current];
      if (!slide) {
        return;
      }

      progress.textContent = (state.current + 1) + ' / ' + config.slides.length;
      title.textContent = slide.title || '';
      renderToc();

      if (inPreviewGate()) {
        setPreviewGateUI(true);
        updateButtons();
        if (focusPanel) {
          slidePanel.focus();
        }
        return;
      }

      setPreviewGateUI(false);
      setLoading(true);
      body.innerHTML = slide.body_html || '';
      imageWrap.hidden = false;

      if (slide.image_url) {
        image.loading = state.current === introIndex ? 'eager' : 'lazy';
        image.src = slide.image_url;
        image.alt = slide.title || '';
        image.hidden = false;
        imagePlaceholder.hidden = true;
      } else {
        image.removeAttribute('src');
        image.alt = '';
        image.hidden = true;
        imagePlaceholder.hidden = false;
      }

      var audioUrl = getAudioUrl(slide);
      if (!audioUrl) {
        showMessage('لا يوجد ملف صوتي للشريحة الحالية.');
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
      preloadNeighbors(state.current);

      if (focusPanel) {
        slidePanel.focus();
      }
    }

    function updateButtons() {
      var prevBtn = mountNode.querySelector('[data-action="prev"]');
      var playBtn = mountNode.querySelector('[data-action="play"]');
      var muteBtn = mountNode.querySelector('[data-action="mute"]');
      var nextBtn = mountNode.querySelector('[data-action="next"]');
      var menuBtn = mountNode.querySelector('[data-action="toggle-sidebar"]');
      var restartBtn = mountNode.querySelector('[data-action="restart"]');

      prevBtn.disabled = state.current === 0;
      nextBtn.disabled = state.current >= config.slides.length - 1;
      setButtonContent(prevBtn, 'prev', 'السابق');
      setButtonContent(nextBtn, 'next', 'التالي');
      setButtonContent(playBtn, state.playing ? 'pause' : 'play', state.playing ? 'إيقاف' : 'تشغيل');
      setButtonContent(muteBtn, state.muted ? 'mute' : 'unmute', state.muted ? 'إلغاء الكتم' : 'كتم');
      setButtonContent(menuBtn, 'menu', 'الفهرس');
      setButtonContent(restartBtn, 'restart', 'إعادة');

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

    function changeSlide(nextIndex, focusPanel, shouldAutoplay) {
      if (nextIndex < 0 || nextIndex >= config.slides.length) {
        return;
      }
      pauseCurrent();
      state.current = nextIndex;
      renderSlide(focusPanel);
      if (shouldAutoplay) {
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

    if (loginLink) {
      loginLink.href = loginUrl;
    }

    heading.textContent = meta.title || 'التدريب التفاعلي';

    var introSlide = config.slides[introIndex] || config.slides[0];
    if (introSlide && introSlide.image_url) {
      startImageWrap.hidden = false;
      startImage.src = introSlide.image_url;
      startImage.alt = introSlide.title || '';
      startImage.hidden = false;
      startImagePlaceholder.hidden = true;
      preloadImage(introSlide.image_url);
    } else {
      startImageWrap.hidden = false;
      startImage.hidden = true;
      startImagePlaceholder.hidden = false;
    }
    preloadAudio(getAudioUrl(introSlide));

    if (suggestedResumeSlide > 0) {
      state.promptVisible = true;
      resumeBox.hidden = false;
    }

    renderToc();
    setSidebar(state.sidebarOpen);

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
        state.current = introIndex;
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

      if (action === 'toggle-sidebar') {
        setSidebar(!state.sidebarOpen);
      } else if (action === 'prev') {
        changeSlide(state.current - 1, true, true);
      } else if (action === 'next') {
        changeSlide(state.current + 1, true, true);
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
        changeSlide(introIndex, true, true);
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

    toc.addEventListener('click', function (event) {
      var item = event.target.closest('[data-slide-index]');
      if (!item || !state.started) {
        return;
      }
      var nextIndex = parseInt(item.getAttribute('data-slide-index') || '', 10);
      if (isNaN(nextIndex)) {
        return;
      }
      changeSlide(nextIndex, true, false);
      if (window.matchMedia('(max-width: 920px)').matches) {
        setSidebar(false);
      }
    });

    sidebarOverlay.addEventListener('click', function () {
      setSidebar(false);
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
        changeSlide(state.current + 1, true, true);
      } else if (event.key === 'ArrowLeft') {
        event.preventDefault();
        changeSlide(state.current - 1, true, true);
      }
    });

    audio.addEventListener('play', function () {
      state.playing = true;
      updateButtons();
      setLoading(false);
    });

    audio.addEventListener('pause', function () {
      state.playing = false;
      updateButtons();
    });

    audio.addEventListener('loadeddata', function () {
      setLoading(false);
    });

    image.addEventListener('load', function () {
      setLoading(false);
    });

    image.addEventListener('error', function () {
      setLoading(false);
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
      if (!!meta.autoadvance && state.current < config.slides.length - 1) {
        changeSlide(state.current + 1, true, true);
      }
    });

    if (Array.isArray(config.messages) && config.messages.length > 0) {
      showMessage(config.messages.join(' | '));
      config.messages.forEach(function (msg) {
        console.warn('Habaq Training:', msg);
      });
    }

    updateButtons();
    preloadNeighbors(state.current);
  }

  document.querySelectorAll('.habaq-training[data-habaq-training="1"]').forEach(initTrainingPlayer);
})();
