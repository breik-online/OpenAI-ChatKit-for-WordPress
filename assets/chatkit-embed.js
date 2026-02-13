(function() {
  'use strict';

  const config = typeof chatkitConfig !== 'undefined' ? chatkitConfig : {};
  let isOpen = false;
  let retryCount = 0;
  const MAX_RETRIES = 3;

  // Helper to convert WordPress boolean strings to actual booleans
  function toBool(value) {
    if (typeof value === 'boolean') return value;
    if (typeof value === 'string') return value === '1' || value.toLowerCase() === 'true';
    if (typeof value === 'number') return value === 1;
    return !!value;
  }

  function loadChatkitScript() {
    return new Promise((resolve, reject) => {
      if (customElements.get('openai-chatkit')) {
        resolve();
        return;
      }

      const script = document.createElement('script');
      script.src = 'https://cdn.platform.openai.com/deployments/chatkit/chatkit.js';
      script.defer = true;
      script.onload = resolve;
      script.onerror = () => reject(new Error('Failed to load ChatKit CDN'));
      document.head.appendChild(script);
    });
  }

  async function getClientSecret() {
    try {
      if (!config.restUrl) {
        throw new Error('Missing configuration');
      }

      const headers = {
        'Content-Type': 'application/json'
      };

      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 10000);

      const response = await fetch(config.restUrl, {
        method: 'POST',
        headers: headers,
        signal: controller.signal,
        credentials: 'same-origin'
      });

      clearTimeout(timeoutId);

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        
        console.error('ChatKit Session Error:', {
          status: response.status,
          statusText: response.statusText,
          error: errorData
        });
        
        throw new Error(errorData.message || `HTTP ${response.status}`);
      }

      const data = await response.json();
      
      if (!data.client_secret) {
        throw new Error('Invalid response: missing client_secret');
      }
      
      return data.client_secret;

    } catch (error) {
      console.error('Fetch Session Error:', error);

      const errorMessage = config.i18n?.unableToStart || '⚠️ Unable to start chat. Please try again later.';
      
      const el = document.getElementById('myChatkit');
      if (el && el.parentNode) {
        const errorDiv = document.createElement('div');
        errorDiv.style.cssText = 'padding: 20px; text-align: center; color: #721c24; background: #f8d7da; border-radius: 8px; margin: 20px;';
        errorDiv.setAttribute('role', 'alert');
        errorDiv.innerHTML = `<p style="margin: 0; font-size: 14px;">${errorMessage}</p>`;
        el.parentNode.insertBefore(errorDiv, el);
      }

      if (typeof gtag !== 'undefined') {
        gtag('event', 'exception', {
          description: 'ChatKit session error: ' + error.message,
          fatal: false
        });
      }

      return null;
    }
  }

  function setupToggle() {
    const button = document.getElementById('chatToggleBtn');
    const chatkit = document.getElementById('myChatkit');

    if (!button || !chatkit) {
      console.warn('ChatKit toggle elements not found');
      return;
    }

    const originalText = button.textContent || config.buttonText || 'Chat now';
    const closeText = config.closeText || '✕';
    const accentColor = config.accentColor || '#FF4500';

    button.addEventListener('click', () => {
      isOpen = !isOpen;
      chatkit.style.display = isOpen ? 'block' : 'none';
      button.setAttribute('aria-expanded', isOpen);
      chatkit.setAttribute('aria-modal', isOpen);
      
      if (isOpen) {
        button.classList.add('chatkit-open');
        button.textContent = closeText;
        button.style.backgroundColor = accentColor;
        chatkit.style.animation = 'chatkit-slide-up 0.3s ease-out';
        
        setTimeout(() => chatkit.focus(), 100);
        
        if (window.innerWidth <= 768) {
          document.body.style.overflow = 'hidden';
        }
      } else {
        button.classList.remove('chatkit-open');
        button.textContent = originalText;
        button.style.backgroundColor = accentColor;
        button.focus();
        document.body.style.overflow = '';
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && isOpen) {
        button.click();
      }
    });

    document.addEventListener('click', (e) => {
      if (isOpen && 
          !chatkit.contains(e.target) && 
          !button.contains(e.target)) {
        button.click();
      }
    });
  }

  function buildPrompts() {
    const prompts = [];

    // Support for new array format
    if (config.prompts && Array.isArray(config.prompts) && config.prompts.length > 0) {
      config.prompts.forEach(prompt => {
        if (prompt && prompt.label && prompt.text) {
          prompts.push({
            icon: prompt.icon || 'circle-question',
            label: prompt.label,
            prompt: prompt.text
          });
        }
      });
    } 
    // Fallback to old format
    else {
      if (config.defaultPrompt1 && config.defaultPrompt1Text) {
        prompts.push({
          icon: 'circle-question',
          label: config.defaultPrompt1,
          prompt: config.defaultPrompt1Text
        });
      }

      if (config.defaultPrompt2 && config.defaultPrompt2Text) {
        prompts.push({
          icon: 'circle-question',
          label: config.defaultPrompt2,
          prompt: config.defaultPrompt2Text
        });
      }

      if (config.defaultPrompt3 && config.defaultPrompt3Text) {
        prompts.push({
          icon: 'circle-question',
          label: config.defaultPrompt3,
          prompt: config.defaultPrompt3Text
        });
      }
    }

    // Fallback default
    if (prompts.length === 0) {
      prompts.push({
        icon: 'circle-question',
        label: 'How can I assist you?',
        prompt: 'Hi! How can I assist you today?'
      });
    }

    return prompts;
  }

  function showUserError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.style.cssText = 'position: fixed; bottom: 20px; right: 20px; padding: 15px 20px; background: #f8d7da; color: #721c24; border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,0.15); z-index: 9999; max-width: 300px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;';
    errorDiv.setAttribute('role', 'alert');
    errorDiv.innerHTML = `<p style="margin: 0; font-size: 14px;">${message}</p>`;
    document.body.appendChild(errorDiv);

    setTimeout(() => {
      if (errorDiv.parentNode) {
        errorDiv.style.opacity = '0';
        errorDiv.style.transition = 'opacity 0.3s ease';
        setTimeout(() => errorDiv.remove(), 300);
      }
    }, 5000);
  }

  async function initEmbeddedChatKit(elementId) {
    try {
      if (!config.restUrl) return;

      await loadChatkitScript();

      if (!customElements.get('openai-chatkit')) {
        await customElements.whenDefined('openai-chatkit');
      }

      // Small delay to ensure element is fully ready
      await new Promise(resolve => setTimeout(resolve, 100));

      const el = document.getElementById(elementId);
      if (!el) return;

      if (typeof el.setOptions !== 'function') {
        // Element not yet upgraded -- retry once after a delay
        setTimeout(() => {
          if (typeof el.setOptions === 'function') {
            el.setOptions(buildChatkitOptions());
          }
        }, 1000);
        return;
      }

      el.setOptions(buildChatkitOptions());

    } catch (error) {
      console.error('ChatKit embedded init error:', error);
    }
  }

  function buildChatkitOptions() {
    const options = {
      api: {
        getClientSecret: getClientSecret
      },
      theme: {
        colorScheme: config.themeMode || 'dark',
        radius: 'round',
        density: 'normal',
        color: {
          accent: {
            primary: config.accentColor || '#FF4500',
            level: parseInt(config.accentLevel) || 2
          }
        },
        typography: {
          baseSize: 16,
          fontFamily: '"OpenAI Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif'
        }
      },
      composer: {
        attachments: {
          enabled: toBool(config.enableAttachments)
        },
        placeholder: config.placeholderText || 'Send a message...'
      },
      startScreen: {
        greeting: config.greetingText || 'How can I help you today?',
        prompts: buildPrompts()
      }
    };

    // File upload configuration
    if (toBool(config.enableAttachments)) {
      try {
        const maxSize = parseInt(config.attachmentMaxSize) || 20;
        const maxCount = parseInt(config.attachmentMaxCount) || 3;
        
        options.composer.attachments = {
          enabled: true,
          maxSize: maxSize * 1024 * 1024,
          maxCount: maxCount,
          accept: {
            'application/pdf': ['.pdf'],
            'image/*': ['.png', '.jpg', '.jpeg', '.gif', '.webp'],
            'text/plain': ['.txt']
          }
        };
        
      } catch (e) {
        console.warn('Attachments config error, using basic mode:', e);
      }
    }

    // Initial thread ID
    if (config.initialThreadId && config.initialThreadId.trim() !== '') {
      options.initialThread = config.initialThreadId;
    }

    // Disclaimer
    if (config.disclaimerText && config.disclaimerText.trim() !== '') {
      options.disclaimer = {
        text: config.disclaimerText,
        highContrast: toBool(config.disclaimerHighContrast)
      };
    }

    // Custom typography
    if (config.customFont && config.customFont.fontFamily && config.customFont.fontFamily.trim() !== '') {
      try {
        options.theme.typography = {
          fontFamily: config.customFont.fontFamily,
          baseSize: parseInt(config.customFont.baseSize) || 16
        };
      } catch (e) {
        console.warn('Typography config error, using default:', e);
      }
    }

    // Header configuration
    if (toBool(config.showHeader)) {
      const headerConfig = { enabled: true };
      
      if (config.headerTitleText && config.headerTitleText.trim() !== '') {
        headerConfig.title = {
          enabled: true,
          text: config.headerTitleText
        };
      }
      
      if (config.headerLeftIcon && config.headerLeftUrl && config.headerLeftUrl.trim() !== '') {
        try {
          new URL(config.headerLeftUrl);
          headerConfig.leftAction = {
            icon: config.headerLeftIcon,
            onClick: () => {
              window.location.href = config.headerLeftUrl;
            }
          };
        } catch (e) {
          // Invalid URL, skip left button
        }
      }
      
      if (config.headerRightIcon && config.headerRightUrl && config.headerRightUrl.trim() !== '') {
        try {
          new URL(config.headerRightUrl);
          headerConfig.rightAction = {
            icon: config.headerRightIcon,
            onClick: () => {
              window.location.href = config.headerRightUrl;
            }
          };
        } catch (e) {
          // Invalid URL, skip right button
        }
      }

      options.header = headerConfig;
    } else {
      options.header = { enabled: false };
    }

    // History
    options.history = { 
      enabled: toBool(config.historyEnabled) 
    };

    // Locale
    if (config.locale && config.locale.trim() !== '') {
      options.locale = config.locale;
    }

    return options;
  }

  async function initChatKit() {
    try {
      if (!config.restUrl) {
        const errorMsg = config.i18n?.configError || 'Chat configuration error. Please contact support.';
        showUserError(errorMsg);
        return;
      }

      await loadChatkitScript();

      if (!customElements.get('openai-chatkit')) {
        await customElements.whenDefined('openai-chatkit');
      }

      const chatkitElement = document.getElementById('myChatkit');
      if (!chatkitElement) {
        if (retryCount < MAX_RETRIES) {
          retryCount++;
          setTimeout(initChatKit, 1000);
        }
        return;
      }

      setupToggle();

      const options = buildChatkitOptions();
      chatkitElement.setOptions(options);

      if (typeof gtag !== 'undefined') {
        gtag('event', 'chatkit_initialized', {
          event_category: 'engagement',
          event_label: 'ChatKit Ready'
        });
      }

    } catch (error) {
      console.error('ChatKit init error:', error);

      if (retryCount < MAX_RETRIES) {
        retryCount++;
        setTimeout(initChatKit, 2000);
      } else {
        const errorMsg = config.i18n?.loadFailed || 'Chat widget failed to load. Please refresh the page.';
        showUserError(errorMsg);
      }
    }
  }

  function initAllChatKits() {
    const hasStandardWidget = document.getElementById('myChatkit');
    const embeddedWrappers = document.querySelectorAll('[data-chatkit-embedded]');

    if (!hasStandardWidget && embeddedWrappers.length === 0) return;

    if (hasStandardWidget) {
      initChatKit();
    }

    embeddedWrappers.forEach(function(wrapper) {
      const elementId = wrapper.getAttribute('data-chatkit-embedded');
      if (elementId) {
        initEmbeddedChatKit(elementId);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAllChatKits);
  } else {
    setTimeout(initAllChatKits, 0);
  }

  window.addEventListener('beforeunload', () => {
    document.body.style.overflow = '';
  });
})();
