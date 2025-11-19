<?php
// footer.php - include at bottom of public pages
?>
    <footer class="footer card">
      &copy; <?=date('Y')?> AMS - Built for demo. Teacher Mir.
    </footer>
  </div>
  <div class="confirm-overlay" data-confirm-overlay aria-hidden="true">
    <div class="confirm-dialog" data-confirm-dialog role="dialog" aria-modal="true" aria-labelledby="confirm-dialog-title" aria-describedby="confirm-dialog-message">
      <p class="confirm-eyebrow">Safety check</p>
      <h3 class="confirm-title" id="confirm-dialog-title" data-confirm-title>Confirm action</h3>
      <p class="confirm-body" id="confirm-dialog-message" data-confirm-message>Are you sure you want to continue?</p>
      <div class="confirm-actions">
        <button type="button" class="confirm-btn secondary" data-confirm-cancel>Cancel</button>
        <button type="button" class="confirm-btn primary" data-confirm-accept>Confirm</button>
      </div>
    </div>
  </div>
  <script>
    (function () {
      const overlay = document.querySelector('[data-confirm-overlay]');
      if (!overlay) return;

      const dialog = overlay.querySelector('[data-confirm-dialog]');
      const titleNode = overlay.querySelector('[data-confirm-title]');
      const messageNode = overlay.querySelector('[data-confirm-message]');
      const confirmBtn = overlay.querySelector('[data-confirm-accept]');
      const cancelBtn = overlay.querySelector('[data-confirm-cancel]');
      const visibleClass = 'is-visible';
      let activeTrigger = null;

      const closeDialog = () => {
        overlay.classList.remove(visibleClass);
        overlay.setAttribute('aria-hidden', 'true');
        if (activeTrigger && typeof activeTrigger.focus === 'function') {
          activeTrigger.focus();
        }
        activeTrigger = null;
      };

      const openDialog = (trigger) => {
        activeTrigger = trigger;
        const title = trigger.getAttribute('data-confirm-title') || 'Please confirm';
        const message = trigger.getAttribute('data-confirm') || 'Are you sure you want to continue?';
        const cta = trigger.getAttribute('data-confirm-cta') || 'Confirm';
        const style = (trigger.getAttribute('data-confirm-style') || '').toLowerCase();

        if (titleNode) titleNode.textContent = title;
        if (messageNode) messageNode.textContent = message;
        if (confirmBtn) confirmBtn.textContent = cta;
        if (dialog) dialog.classList.toggle('is-danger', style === 'danger');

        overlay.classList.add(visibleClass);
        overlay.removeAttribute('aria-hidden');
        if (confirmBtn && typeof confirmBtn.focus === 'function') {
          confirmBtn.focus({ preventScroll: true });
        }
      };

      const handleConfirm = () => {
        if (!activeTrigger) return;
        const shouldSubmit = activeTrigger.dataset.confirmAction === 'submit' || activeTrigger.dataset.confirmSubmit === 'form';
        if (shouldSubmit) {
          const form = activeTrigger.closest('form');
          if (form) form.submit();
        } else if (activeTrigger.hasAttribute('href')) {
          const href = activeTrigger.getAttribute('href');
          if (href) {
            window.location.href = href;
          }
        } else if (activeTrigger.dataset.confirmTarget) {
          const target = document.querySelector(activeTrigger.dataset.confirmTarget);
          if (target && typeof target.click === 'function') {
            target.click();
          }
        }
        closeDialog();
      };

      if (confirmBtn) {
        confirmBtn.addEventListener('click', handleConfirm);
      }
      if (cancelBtn) {
        cancelBtn.addEventListener('click', closeDialog);
      }

      overlay.addEventListener('click', (event) => {
        if (event.target === overlay) {
          closeDialog();
        }
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && overlay.classList.contains(visibleClass)) {
          closeDialog();
        }
      });

      document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-confirm]');
        if (!trigger || trigger.disabled) return;
        event.preventDefault();
        openDialog(trigger);
      });
    })();
  </script>
</body>
</html>
