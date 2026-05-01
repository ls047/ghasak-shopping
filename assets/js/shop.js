/**
 * Storefront enhancements (optional).
 */
document.addEventListener('DOMContentLoaded', () => {
  const badge = document.getElementById('cart-badge');
  if (!badge) return;
  const n = parseInt(badge.textContent || '0', 10);
  badge.style.display = n > 0 ? 'inline-block' : 'inline-block';
});
