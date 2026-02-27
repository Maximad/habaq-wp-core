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

  function normalizeDigits(value) {
    return String(value || '')
      .replace(/[٠-٩]/g, function (ch) { return String(ch.charCodeAt(0) - 1632); })
      .replace(/[۰-۹]/g, function (ch) { return String(ch.charCodeAt(0) - 1776); });
  }

  function leadingNumeric(value) {
    var normalized = normalizeDigits(value).trim();
    var match = normalized.match(/^(\d+)/);
    return match ? parseInt(match[1], 10) : null;
  }

  function icon(name) {
    var icons = {
      play: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"></path></svg>',
      pause: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5h4v14H7zm6 0h4v14h-4z"></path></svg>',
      prev: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.5 6.5 10 12l5.5 5.5-1.5 1.5L7 12l7-7z"></path></svg>',
      next: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8.5 6.5 1.5-1.5 7 7-7 7-1.5-1.5L14 12z"></path></svg>',
      mute: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 9v6h4l5 4V5L9 9zm10.5 3 2.5 2.5-1.5 1.5-2.5-2.5-2.5 2.5-1.5-1.5 2.5-2.5-2.5-2.5 1.5-1.5 2.5 2.5 2.5-2.5 1.5 1.5z"></path></svg>',
      unmute: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 9v6h4l5 4V5L9 9zm11.5 3a3.5 3.5 0 0 0-1.75-3.03v6.06A3.5 3.5 0 0 0 16.5 12zm0-7a10.5 10.5 0 0 1 0 14l-1.42-1.42a8.5 8.5 0 0 0 0-11.16z"></path></svg>',
      menu: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"></path></svg>',
      fullscreen: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 14H5v5h5v-2H7zm0-4h2V7h3V5H5v5zm10 7h-3v2h5v-5h-2zm0-12V5h-5v2h3v3h2z"></path></svg>',
      minimize: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 16h3v3h2v-5H8zm0-8h5V3h-2v3H8zm8 8h-5v5h2v-3h3zm-3-8h5V3h-2v3h-3z"></path></svg>',
      video: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h13v12H3zm15 4 3-2v8l-3-2z"></path></svg>',
      close: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m18.3 5.71-1.41-1.42L12 9.17 7.11 4.29 5.7 5.71 10.59 10.6 5.7 15.49l1.41 1.42L12 12l4.89 4.91 1.41-1.42-4.89-4.89z"></path></svg>'
    };

    return icons[name] || '';
  }

  function setIconButton(button, iconName, label) {
    if (!button) {
      return;
    }
    button.setAttribute('aria-label', label);
    button.innerHTML = '<span class="habaq-training__button-icon">' + icon(iconName) + '</span><span class="habaq-training__button-tip">' + label + '</span>';
  }

  function initTrainingPlayer(container) {
    var configNode = container.querySelector('.habaq-training-config');
    var mountNode = container.querySelector('.habaq-training__app');
    var fallbackNode = container.querySelector('.habaq-training__fallback');

    if (!configNode || !mountNode) {
      return;
    }

    var config = parseConfig(configNode);
    if (!config || !Array.isArray(config.slides) || config.slides.length === 0) {
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

    var bootstrap = window.habaqTraining || {};
    var isLoggedIn = !!viewer.is_logged_in;
    var canTrackServer = !!viewer.can_track_server;
    var ajaxUrl = bootstrap.ajaxUrl || '';
    var nonce = bootstrap.nonce || '';
    var baseIndex = parseInt(meta.index_base || 1, 10);
    var isCinematic = meta.ui === 'cinematic';
    var hasAnyVideo = !!config.videoMap && Object.keys(config.videoMap).length > 0;

    var introIndex = 0;
    config.slides.forEach(function (slide, idx) {
      if (slide && Number(slide.audio_index) === 1 && introIndex === 0) {
        introIndex = idx;
      }
    });

    var storageKey = 'habaq_training_progress:' + (config.slug || 'default');
    var uiStorageKey = 'habaq_training_ui:' + (config.slug || 'default');

    var localSaved = null;
    var uiState = { immersive: false, sidebarOpen: false, videoEnabled: true };
    try {
      localSaved = JSON.parse(window.localStorage.getItem(storageKey) || 'null');
      var parsedUi = JSON.parse(window.localStorage.getItem(uiStorageKey) || 'null');
      if (parsedUi && typeof parsedUi === 'object') {
        uiState.immersive = !!parsedUi.immersive;
        uiState.sidebarOpen = !!parsedUi.sidebarOpen;
        if (typeof parsedUi.videoEnabled !== 'undefined') {
          uiState.videoEnabled = !!parsedUi.videoEnabled;
        }
      }
    } catch (e) {
      localSaved = null;
    }

    var serverSlide = (config.resume && typeof config.resume.current_slide !== 'undefined') ? parseInt(config.resume.current_slide, 10) : introIndex;
    var localSlide = (localSaved && typeof localSaved.current_slide !== 'undefined') ? parseInt(localSaved.current_slide, 10) : introIndex;
    if (isNaN(serverSlide) || serverSlide < 0) { serverSlide = introIndex; }
    if (isNaN(localSlide) || localSlide < 0) { localSlide = introIndex; }

    var state = {
      started: false,
      current: introIndex,
      playing: false,
      muted: false,
      completed: !!(config.resume && config.resume.completed),
      completedAt: (config.resume && config.resume.completed_at) ? parseInt(config.resume.completed_at, 10) : 0,
      lastServerSaveAt: 0,
      promptVisible: false,
      sidebarOpen: !!uiState.sidebarOpen,
      immersive: isCinematic ? true : !!uiState.immersive,
      pendingMedia: false,
      pendingImage: false,
      pendingAudio: false,
      videoEnabled: !!uiState.videoEnabled,
      videoMuted: true
    };

    var suggestedResumeSlide = isLoggedIn ? serverSlide : localSlide;
    if (suggestedResumeSlide >= config.slides.length) {
      suggestedResumeSlide = config.slides.length - 1;
    }

    mountNode.innerHTML = [
      '<div class="habaq-training__shell" data-shell>',
      '  <section class="habaq-training__stage" data-stage>',
      '    <video class="habaq-training__bg-video" data-bg-video muted playsinline preload="metadata" loop hidden></video>',
      '    <div class="habaq-training__bg-image" data-bg-image></div>',
      '    <div class="habaq-training__overlay"></div>',
      '    <header class="habaq-training__hud-top">',
      '      <span class="habaq-training__tiny-title" data-tiny-title></span>',
      '      <div class="habaq-training__hud-actions">',
      '        <button type="button" class="habaq-training__icon-btn" data-action="toggle-sidebar"></button>',
      '        <button type="button" class="habaq-training__icon-btn" data-action="toggle-fullscreen"></button>',
      '        <button type="button" class="habaq-training__icon-btn" data-action="close-immersive"></button>',
      '      </div>',
      '    </header>',
      '    <div class="habaq-training__content" data-content>',
      '      <div class="habaq-training__start" data-start-screen="1">',
      '        <h2 class="habaq-training__heading"></h2>',
      '        <p class="habaq-training__hint">اضغط للبدء ثم تنقّل بين الشرائح.</p>',
      '        <div class="habaq-training__resume" data-resume hidden><p class="habaq-training__hint">متابعة من حيث توقفت؟</p><div class="habaq-training__resume-actions"><button type="button" class="habaq-training__button" data-action="resume-continue">متابعة</button><button type="button" class="habaq-training__button" data-action="resume-restart">بدء من البداية</button></div></div>',
      '        <button type="button" class="habaq-training__button habaq-training__button--primary" data-action="start">ابدأ التدريب</button>',
      '      </div>',
      '      <article class="habaq-training__slide" data-slide-panel tabindex="-1" hidden>',
      '        <div class="habaq-training__loading" data-loading hidden><span class="habaq-training__skeleton habaq-training__skeleton--media"></span><span class="habaq-training__skeleton habaq-training__skeleton--line"></span></div>',
      '        <div class="habaq-training__preview-gate" data-preview-gate hidden><p class="habaq-training__gate-message">هذا التدريب متاح لأعضاء الفريق فقط.</p><a class="habaq-training__button habaq-training__button--primary" data-login-link href="#">تسجيل الدخول</a></div>',
      '        <div class="habaq-training__body" data-body></div>',
      '      </article>',
      '    </div>',
      '    <footer class="habaq-training__hud-bottom">',
      '      <div class="habaq-training__thin-progress"><span data-thin-progress></span></div>',
      '      <div class="habaq-training__controls">',
      '        <button type="button" class="habaq-training__icon-btn" data-action="prev"></button>',
      '        <button type="button" class="habaq-training__icon-btn" data-action="play"></button>',
      '        <button type="button" class="habaq-training__icon-btn" data-action="next"></button>',
      '        <button type="button" class="habaq-training__icon-btn" data-action="mute"></button>',
      '        <button type="button" class="habaq-training__icon-btn" data-action="video" hidden></button>',
      '      </div>',
      '      <div class="habaq-training__status"><span data-progress></span><span data-title></span></div>',
      '      <input data-seek type="range" min="0" max="100" value="0" step="1" />',
      '      <div class="habaq-training__complete" data-complete-wrap hidden><label class="habaq-training__ack"><input type="checkbox" data-ack /> أُقِرّ بأنني اطّلعت على هذا التدريب وأفهم التزاماتي.</label><button type="button" class="habaq-training__button habaq-training__button--primary" data-action="finish">إنهاء التدريب</button><p class="habaq-training__badge" data-complete-badge hidden>مكتمل ✓</p></div>',
      '      <audio data-audio preload="metadata"></audio>',
      '      <p class="habaq-training__message" data-message hidden></p>',
      '    </footer>',
      '  </section>',
      '  <aside class="habaq-training__sidebar" data-sidebar><div class="habaq-training__sidebar-header">الفهرس</div><ol class="habaq-training__toc" data-toc></ol></aside>',
      '  <button type="button" class="habaq-training__sidebar-overlay" data-sidebar-overlay hidden aria-label="إغلاق الفهرس"></button>',
      '</div>'
    ].join('');

    var shell = mountNode.querySelector('[data-shell]');
    var startScreen = mountNode.querySelector('[data-start-screen]');
    var slidePanel = mountNode.querySelector('[data-slide-panel]');
    var heading = mountNode.querySelector('.habaq-training__heading');
    var tinyTitle = mountNode.querySelector('[data-tiny-title]');
    var progress = mountNode.querySelector('[data-progress]');
    var title = mountNode.querySelector('[data-title]');
    var body = mountNode.querySelector('[data-body]');
    var stage = mountNode.querySelector('[data-stage]');
    var bgVideo = mountNode.querySelector('[data-bg-video]');
    var bgImage = mountNode.querySelector('[data-bg-image]');
    var loadingEl = mountNode.querySelector('[data-loading]');
    var seek = mountNode.querySelector('[data-seek]');
    var thinProgress = mountNode.querySelector('[data-thin-progress]');
    var audio = mountNode.querySelector('[data-audio]');
    var message = mountNode.querySelector('[data-message]');
    var previewGate = mountNode.querySelector('[data-preview-gate]');
    var loginLink = mountNode.querySelector('[data-login-link]');
    var completeWrap = mountNode.querySelector('[data-complete-wrap]');
    var ack = mountNode.querySelector('[data-ack]');
    var completeBadge = mountNode.querySelector('[data-complete-badge]');
    var resumeBox = mountNode.querySelector('[data-resume]');
    var sidebar = mountNode.querySelector('[data-sidebar]');
    var sidebarOverlay = mountNode.querySelector('[data-sidebar-overlay]');
    var toc = mountNode.querySelector('[data-toc]');
    var videoBtn = mountNode.querySelector('[data-action="video"]');

    var preloadLinks = {};

    function getMapUrl(map, index) {
      if (!map || index === null || typeof index === 'undefined') {
        return '';
      }
      var entry = map[String(index)] || map[index];
      if (!entry) {
        return '';
      }
      return typeof entry === 'string' ? entry : (entry.url || '');
    }

    function resolveIndexes(slide, computedIndex) {
      var audioIndex = leadingNumeric(slide && slide.audio_index);
      if (audioIndex === null) {
        audioIndex = computedIndex;
      }

      var imageIndex = leadingNumeric(slide && slide.image_index);
      if (imageIndex === null) {
        imageIndex = leadingNumeric(slide && slide.image_url);
      }
      if (imageIndex === null) {
        imageIndex = audioIndex;
      }
      if (imageIndex === null) {
        imageIndex = computedIndex;
      }

      var videoIndex = leadingNumeric(slide && slide.video_index);
      if (videoIndex === null) {
        videoIndex = leadingNumeric(slide && slide.video);
      }

      return { audioIndex: audioIndex, imageIndex: imageIndex, videoIndex: videoIndex };
    }

    function saveUiState() {
      window.localStorage.setItem(uiStorageKey, JSON.stringify({
        immersive: state.immersive,
        sidebarOpen: state.sidebarOpen,
        videoEnabled: state.videoEnabled
      }));
    }

    function preloadImage(url) {
      if (!url) { return; }
      var probe = new Image();
      probe.src = url;
    }

    function preloadAudio(url) {
      if (!url || preloadLinks[url]) { return; }
      var link = document.createElement('link');
      link.rel = 'preload';
      link.as = 'audio';
      link.href = url;
      document.head.appendChild(link);
      preloadLinks[url] = link;
    }

    function preloadVideo(url) {
      if (!url || preloadLinks[url] || !state.videoEnabled) { return; }
      var link = document.createElement('link');
      link.rel = 'preload';
      link.as = 'video';
      link.href = url;
      document.head.appendChild(link);
      preloadLinks[url] = link;
    }

    function preloadNeighbors(index) {
      var currentSlide = config.slides[index];
      var nextSlide = config.slides[index + 1];

      if (currentSlide) {
        var currentIndexes = resolveIndexes(currentSlide, baseIndex + index);
        preloadImage(getMapUrl(config.imageMap, currentIndexes.imageIndex));
      }

      if (nextSlide) {
        var nextIndexes = resolveIndexes(nextSlide, baseIndex + index + 1);
        preloadImage(getMapUrl(config.imageMap, nextIndexes.imageIndex));
        preloadAudio(getMapUrl(config.audioMap, nextIndexes.audioIndex));
        preloadVideo(getMapUrl(config.videoMap, nextIndexes.videoIndex));
      }
    }

    function updateVh() {
      container.style.setProperty('--habaq-vh', (window.innerHeight * 0.01) + 'px');
    }

    function setImmersive(enabled) {
      state.immersive = !!enabled;
      container.classList.toggle('habaq-training--immersive', state.immersive);
      if (state.immersive) {
        document.body.dataset.habaqTrainingOverflow = document.body.style.overflow || '';
        document.body.style.overflow = 'hidden';
      } else if (!document.fullscreenElement) {
        document.body.style.overflow = document.body.dataset.habaqTrainingOverflow || '';
      }
      saveUiState();
      updateVh();
      updateButtons();
    }

    function toggleFullscreen() {
      if (document.fullscreenElement === container) {
        document.exitFullscreen().catch(function () { setImmersive(false); });
        return;
      }

      if (container.requestFullscreen) {
        container.requestFullscreen().catch(function () {
          setImmersive(!state.immersive);
        });
      } else {
        setImmersive(!state.immersive);
      }
    }

    function setSidebar(open) {
      state.sidebarOpen = !!open;
      sidebar.classList.toggle('is-open', state.sidebarOpen);
      sidebarOverlay.hidden = !state.sidebarOpen;
      saveUiState();
    }

    function inPreviewGate() {
      return !isLoggedIn && previewSlides > 0 && state.current >= previewSlides;
    }

    function showMessage(text) {
      message.hidden = !text;
      message.textContent = text || '';
    }

    function setLoading(loading, imagePending, audioPending) {
      state.pendingMedia = !!loading;
      state.pendingImage = !!imagePending;
      state.pendingAudio = !!audioPending;
      loadingEl.hidden = !state.pendingMedia;
      updateButtons();
    }

    function maybeStopLoading() {
      if (!state.pendingImage && !state.pendingAudio) {
        setLoading(false, false, false);
      }
    }

    function setPreviewGateUI(enabled) {
      previewGate.hidden = !enabled;
      body.hidden = enabled;
      seek.disabled = enabled;
      if (enabled) {
        audio.pause();
        audio.removeAttribute('src');
        audio.load();
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
      if (finishButton) {
        finishButton.disabled = mustAck && !ack.checked;
      }
    }

    function saveLocalProgress() {
      window.localStorage.setItem(storageKey, JSON.stringify({
        current_slide: state.current,
        updated_at: nowTs(),
        completed: !!state.completed
      }));
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

    function renderToc() {
      toc.innerHTML = '';
      config.slides.forEach(function (slide, idx) {
        var label = slide.title || ('شريحة ' + (idx + 1));
        var number = leadingNumeric(slide.audio_index);
        if (number === null) {
          number = baseIndex + idx;
        }
        var active = idx === state.current ? ' is-active' : '';
        toc.insertAdjacentHTML('beforeend', '<li><button type="button" class="habaq-training__toc-item' + active + '" data-slide-index="' + idx + '"><span class="habaq-training__toc-num">' + number + '</span><span class="habaq-training__toc-label">' + label + '</span></button></li>');
      });
    }

    function renderStageMedia(slide, computedIndex) {
      var indexes = resolveIndexes(slide, computedIndex);
      var imageUrl = getMapUrl(config.imageMap, indexes.imageIndex);
      var videoUrl = getMapUrl(config.videoMap, indexes.videoIndex);
      var audioUrl = getMapUrl(config.audioMap, indexes.audioIndex);

      bgImage.style.backgroundImage = imageUrl ? 'url(' + JSON.stringify(imageUrl) + ')' : '';
      bgImage.classList.toggle('is-empty', !imageUrl);

      if (state.videoEnabled && videoUrl) {
        bgVideo.hidden = false;
        bgVideo.muted = state.videoMuted;
        bgVideo.loop = meta.video_loop !== false;
        bgVideo.poster = imageUrl || '';
        if (bgVideo.src !== videoUrl) {
          bgVideo.src = videoUrl;
          bgVideo.load();
        }
        bgVideo.play().catch(function () {});
      } else {
        bgVideo.pause();
        bgVideo.hidden = true;
      }

      return {
        imageUrl: imageUrl,
        videoUrl: videoUrl,
        audioUrl: audioUrl
      };
    }

    function renderSlide(focusPanel) {
      var slide = config.slides[state.current];
      if (!slide) {
        return;
      }

      progress.textContent = (state.current + 1) + ' / ' + config.slides.length;
      title.textContent = slide.title || '';
      renderToc();

      var computedIndex = baseIndex + state.current;
      var media = renderStageMedia(slide, computedIndex);

      if (inPreviewGate()) {
        setPreviewGateUI(true);
        updateButtons();
        return;
      }

      setPreviewGateUI(false);
      slidePanel.hidden = false;
      body.innerHTML = slide.body_html || '';
      setLoading(true, !!media.imageUrl, !!media.audioUrl);
      if (media.imageUrl) {
        state.pendingImage = false;
      }

      if (!media.audioUrl) {
        showMessage('لا يوجد ملف صوتي للشريحة الحالية.');
        audio.removeAttribute('src');
        audio.load();
        state.pendingAudio = false;
      } else {
        showMessage('');
        audio.src = media.audioUrl;
        audio.muted = state.muted;
        audio.load();
      }

      if (!media.imageUrl) {
        state.pendingImage = false;
      } else {
        preloadImage(media.imageUrl);
      }

      if (!media.imageUrl && !media.audioUrl) {
        setLoading(false, false, false);
      }

      seek.value = 0;
      thinProgress.style.width = '0%';
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
      var sidebarBtn = mountNode.querySelector('[data-action="toggle-sidebar"]');
      var fullscreenBtn = mountNode.querySelector('[data-action="toggle-fullscreen"]');
      var closeBtn = mountNode.querySelector('[data-action="close-immersive"]');

      setIconButton(prevBtn, 'prev', 'السابق');
      setIconButton(playBtn, state.playing ? 'pause' : 'play', state.playing ? 'إيقاف' : 'تشغيل');
      setIconButton(nextBtn, 'next', 'التالي');
      setIconButton(muteBtn, state.muted ? 'mute' : 'unmute', state.muted ? 'إلغاء الكتم' : 'كتم');
      setIconButton(sidebarBtn, 'menu', 'الفهرس');
      setIconButton(fullscreenBtn, (document.fullscreenElement || state.immersive) ? 'minimize' : 'fullscreen', 'ملء الشاشة');
      setIconButton(closeBtn, 'close', 'إغلاق');

      if (videoBtn) {
        videoBtn.hidden = !hasAnyVideo;
        setIconButton(videoBtn, 'video', state.videoEnabled ? 'الفيديو: تشغيل' : 'الفيديو: إيقاف');
      }

      prevBtn.disabled = state.current === 0;
      nextBtn.disabled = state.current >= config.slides.length - 1 || state.pendingMedia;
      playBtn.disabled = inPreviewGate() || state.pendingMedia;
      muteBtn.disabled = inPreviewGate();
      closeBtn.hidden = !state.immersive && !document.fullscreenElement;

      completeWrap.hidden = state.current !== (config.slides.length - 1) || inPreviewGate();
      renderCompletionState();
    }

    function playCurrent() {
      if (inPreviewGate() || !audio.src || state.pendingMedia) {
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
    tinyTitle.textContent = meta.title || 'التدريب التفاعلي';

    var introSlide = config.slides[introIndex] || config.slides[0];
    if (introSlide) {
      renderStageMedia(introSlide, baseIndex + introIndex);
      var introIndexes = resolveIndexes(introSlide, baseIndex + introIndex);
      preloadImage(getMapUrl(config.imageMap, introIndexes.imageIndex));
      preloadAudio(getMapUrl(config.audioMap, introIndexes.audioIndex));
      preloadVideo(getMapUrl(config.videoMap, introIndexes.videoIndex));
    }

    if (suggestedResumeSlide > 0) {
      state.promptVisible = true;
      resumeBox.hidden = false;
    }

    renderToc();
    setSidebar(state.sidebarOpen);
    setImmersive(state.immersive);
    updateVh();
    updateButtons();

    mountNode.addEventListener('click', function (event) {
      var button = event.target.closest('[data-action]');
      if (!button) {
        return;
      }

      var action = button.getAttribute('data-action');

      if (action === 'toggle-fullscreen') {
        toggleFullscreen();
        return;
      }

      if (action === 'close-immersive') {
        if (document.fullscreenElement === container) {
          document.exitFullscreen().catch(function () {});
        }
        setImmersive(false);
        return;
      }

      if (action === 'toggle-sidebar') {
        setSidebar(!state.sidebarOpen);
        return;
      }

      if (action === 'video') {
        state.videoEnabled = !state.videoEnabled;
        saveUiState();
        renderSlide(false);
        updateButtons();
        return;
      }

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
        slidePanel.hidden = false;
        renderSlide(true);
        playCurrent();
        return;
      }

      if (!state.started) {
        return;
      }

      if (action === 'prev') {
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
      } else if (event.key === ' ') {
        event.preventDefault();
        if (state.playing) {
          pauseCurrent();
        } else {
          playCurrent();
        }
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

    audio.addEventListener('loadeddata', function () {
      state.pendingAudio = false;
      maybeStopLoading();
    });

    bgVideo.addEventListener('loadeddata', function () {
      state.pendingImage = false;
      maybeStopLoading();
    });

    audio.addEventListener('timeupdate', function () {
      if (!audio.duration || !isFinite(audio.duration)) {
        seek.value = 0;
        thinProgress.style.width = '0%';
        return;
      }

      var value = Math.round((audio.currentTime / audio.duration) * 100);
      seek.value = String(value);
      thinProgress.style.width = value + '%';
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

    document.addEventListener('fullscreenchange', function () {
      if (!document.fullscreenElement && !state.immersive) {
        document.body.style.overflow = document.body.dataset.habaqTrainingOverflow || '';
      }
      updateButtons();
      updateVh();
    });

    window.addEventListener('resize', updateVh, { passive: true });
    window.addEventListener('orientationchange', updateVh, { passive: true });

    if (Array.isArray(config.messages) && config.messages.length > 0) {
      showMessage(config.messages.join(' | '));
    }
  }

  document.querySelectorAll('.habaq-training[data-habaq-training="1"]').forEach(initTrainingPlayer);
})();
