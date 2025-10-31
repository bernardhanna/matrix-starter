/* Theme-Forms helper v3 — AJAX submit + banners + CAPTCHA (reCAPTCHA v3 / Cloudflare Turnstile)
 *
 * Provide globals via enqueue:
 *   window.themeFormsCaptchaProvider  = 'none' | 'recaptcha_v3' | 'turnstile'
 *   window.themeFormsRecaptchaV3      = 'YOUR_RECAPTCHA_SITE_KEY'
 *   window.themeFormsTurnstileSiteKey = 'YOUR_TURNSTILE_SITE_KEY'
 */

(function () {
  // Prevent double-init if the file is accidentally enqueued twice.
  if (window._themeFormsInitDone) return;
  window._themeFormsInitDone = true;

  document.addEventListener('DOMContentLoaded', () => {
    let provider = window.themeFormsCaptchaProvider;
    if (!provider || typeof provider !== 'string') provider = 'none';
    provider = provider.toLowerCase();

    let tsSiteKey = window.themeFormsTurnstileSiteKey;
    if (tsSiteKey && typeof tsSiteKey === 'object') {
      tsSiteKey = tsSiteKey.value || tsSiteKey.key || '';
    }
    if (typeof tsSiteKey !== 'string') tsSiteKey = '';
    tsSiteKey = tsSiteKey.trim();

    const reSiteKey = (typeof window.themeFormsRecaptchaV3 === 'string' ? window.themeFormsRecaptchaV3.trim() : '');

    document.querySelectorAll('form[data-theme-form]').forEach(form => {
      form.addEventListener('submit', ev => {
        if (!form.checkValidity()) { ev.preventDefault(); form.reportValidity(); return; }
        ev.preventDefault();
        if (form.dataset.submitting === '1') return; // prevent double submit

        const lock = (on) => {
          form.dataset.submitting = on ? '1' : '0';
          form.classList.toggle('is-submitting', !!on);
          form.querySelectorAll('button, [type="submit"]').forEach(b => b.disabled = !!on);
        };

        // Abort any prior in-flight request for this form
        if (form._submitController) { try { form._submitController.abort(); } catch (e) { } }
        form._submitController = new AbortController();

        const send = () => {
          lock(true);

          const data = new FormData(form);
          data.append('is_ajax', '1');

          fetch(form.getAttribute('action'), {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: form._submitController.signal
          })
            .then(async r => {
              const ct = (r.headers.get('content-type') || '').toLowerCase();
              const body = ct.includes('application/json') ? await r.json() : await r.text();
              const ok = typeof body === 'object' ? !!body.success : false;

              if (ok) {
                showBanner(form, 'Thanks! Your message has been sent.', true);
                form.reset();
                // Reset Turnstile invisible widget if one was rendered
                if (window.turnstile && form._tsWidgetId) {
                  try { window.turnstile.reset(form._tsWidgetId); } catch (e) { }
                }
              } else {
                let msg = 'Sorry, something went wrong. Please try again.';
                if (typeof body === 'object' && body && body.data) {
                  const d = body.data;
                  if (d.mail_error) msg += ` (Mail error: ${d.mail_error})`;
                  if (d.to) msg += ` [to: ${d.to}]`;
                }
                showBanner(form, msg, false);
              }
            })
            .catch((e) => {
              if (e && e.name === 'AbortError') return;
              showBanner(form, 'Sorry, something went wrong. Please try again.', false);
            })
            .finally(() => lock(false));
        };

        // --- CAPTCHA branches ---
        // Google reCAPTCHA v3
        if (provider === 'recaptcha_v3' && window.grecaptcha && reSiteKey) {
          grecaptcha.ready(() => {
            grecaptcha.execute(reSiteKey, { action: 'submit' }).then(token => {
              let inp = form.querySelector('input[name="g-recaptcha-response"]');
              if (!inp) {
                inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'g-recaptcha-response';
                form.appendChild(inp);
              }
              inp.value = token;
              send();
            });
          });
          return;
        }

        // Cloudflare Turnstile (visible or invisible)
        if (provider === 'turnstile' && window.turnstile && tsSiteKey) {
          const existingToken = form.querySelector('input[name="cf-turnstile-response"]');
          if (existingToken && existingToken.value) { send(); return; }

          const placeholder = form.querySelector('.cf-turnstile');

          if (!form._tsWidgetId) {
            form._tsWidgetId = window.turnstile.render(placeholder || form, {
              sitekey: tsSiteKey,
              size: placeholder ? (placeholder.getAttribute('data-size') || 'normal') : 'invisible',
              theme: placeholder ? (placeholder.getAttribute('data-theme') || 'auto') : 'auto',
              callback: (token) => {
                let inp = form.querySelector('input[name="cf-turnstile-response"]');
                if (!inp) {
                  inp = document.createElement('input');
                  inp.type = 'hidden';
                  inp.name = 'cf-turnstile-response';
                  form.appendChild(inp);
                }
                inp.value = token;
                send();
              },
              'error-callback': () => showBanner(form, 'Captcha failed, please try again.', false),
            });
          }

          try {
            // Only execute automatically if invisible
            if (!placeholder) window.turnstile.execute(form._tsWidgetId);
          } catch (e) {
            send();
          }
          return;
        }

        // No CAPTCHA
        send();
      });
    });

    function showBanner(form, text, ok) {
      form.parentElement.querySelectorAll('.theme-form-alert').forEach(el => el.remove());
      const div = document.createElement('div');
      div.className = `theme-form-alert ${ok ? 'is-success' : 'is-error'}`;
      div.textContent = text;
      form.after(div);
      setTimeout(() => div.remove(), 6000);
    }
  });

  // === NEWSLETTER (Brevo) ===
  if (!window._brevoInitDone) {
    window._brevoInitDone = true;

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('form[data-brevo-newsletter]').forEach(form => {
        form.addEventListener('submit', ev => {
          if (!form.checkValidity()) { ev.preventDefault(); form.reportValidity(); return; }
          ev.preventDefault();

          const fd = new FormData(form);
          fd.set('action', 'matrix_subscribe_brevo');
          fd.set('nonce', window.matrixBrevoNonce || '');

          // Abort previous if any
          if (form._brevoController) { try { form._brevoController.abort(); } catch (e) { } }
          form._brevoController = new AbortController();

          fetch((window.ajaxurl || form.action), {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
            signal: form._brevoController.signal
          })
            .then(async r => {
              let out;
              try { out = await r.json(); } catch (e) { out = { success: false }; }
              const ok = out && out.success;
              showBanner(
                form,
                (out && out.data && out.data.message)
                  ? out.data.message
                  : (ok ? 'Thanks, subscribed!' : 'Sorry, something went wrong.'),
                ok
              );
              if (ok) form.reset();
            })
            .catch((e) => {
              if (e && e.name === 'AbortError') return;
              showBanner(form, 'Sorry, something went wrong.', false);
            });
        });
      });

      function showBanner(form, text, ok) {
        const div = document.createElement('div');
        div.className = `theme-form-alert ${ok ? 'is-success' : 'is-error'}`;
        div.textContent = text;
        form.after(div);
        setTimeout(() => div.remove(), 6000);
      }
    });
  }
})();
