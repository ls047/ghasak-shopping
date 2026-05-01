/**
 * Storefront scripts (optional — cart count updates on full page load).
 */
document.addEventListener("DOMContentLoaded", () => {
  const badge = document.getElementById("cart-badge");
  if (!badge) return;
  const n = parseInt(badge.textContent || "0", 10);
  if (n > 0) {
    badge.setAttribute("title", n + " items in cart");
  }
});
