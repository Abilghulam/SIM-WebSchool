// resources/js/ui/loading.js

function createSpinner(size = 18) {
    const span = document.createElement("span");
    span.className = "inline-flex items-center justify-center";
    span.innerHTML = `
        <svg class="animate-spin" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"></path>
        </svg>
    `;
    return span;
}

function setButtonLoading(btn, loadingText = "Memproses...") {
    if (!btn || btn.dataset.loadingApplied === "1") return;

    btn.dataset.loadingApplied = "1";
    btn.dataset.originalHtml = btn.innerHTML;

    const spinner = createSpinner(16);
    btn.innerHTML = "";
    btn.appendChild(spinner);

    const text = document.createElement("span");
    text.textContent = loadingText;
    btn.appendChild(text);

    btn.disabled = true;
    btn.setAttribute("aria-busy", "true");
}

function showScopeOverlay(scopeEl, labelText = "Memuat...") {
    if (!scopeEl) return;

    // pastikan relative supaya overlay "nempel" di scope
    if (getComputedStyle(scopeEl).position === "static") {
        scopeEl.style.position = "relative";
    }

    // kalau sudah ada overlay, jangan bikin lagi
    if (scopeEl.querySelector('[data-loading-overlay="1"]')) return;

    const overlay = document.createElement("div");
    overlay.dataset.loadingOverlay = "1";
    overlay.className =
        // lebih gelap + blur + lebih tinggi z-index + tidak mesti rounded
        "absolute inset-0 z-[999] bg-gray-900/45 backdrop-blur flex items-center justify-center";

    const box = document.createElement("div");
    box.className =
        "flex items-center gap-3 rounded-2xl bg-white/90 px-5 py-3 shadow-lg ring-1 ring-black/5 text-sm font-semibold text-gray-800";

    box.appendChild(createSpinner(20));

    const label = document.createElement("span");
    label.textContent = labelText;
    box.appendChild(label);

    overlay.appendChild(box);
    scopeEl.appendChild(overlay);
}

function getScopeElFromTarget(target) {
    if (!target?.closest) return null;
    return target.closest("[data-loading-scope]");
}

function handleSubmitWithPaint(form, submitter) {
    // cegah double-handle
    if (form.dataset.loadingHandled === "1") return;
    form.dataset.loadingHandled = "1";

    const scopeEl = getScopeElFromTarget(form);
    showScopeOverlay(scopeEl, "Memuat...");

    // set loading di tombol submitter dulu, lalu disable yang lain
    if (submitter && submitter.tagName === "BUTTON") {
        const loadingText = submitter.dataset.loadingText || "Memproses...";
        setButtonLoading(submitter, loadingText);
    } else {
        // fallback: disable semua submit button
        form.querySelectorAll('button[type="submit"]').forEach((btn) => {
            setButtonLoading(btn, btn.dataset.loadingText || "Memproses...");
        });
    }

    // kasih kesempatan browser nge-paint overlay dulu
    requestAnimationFrame(() => {
        form.submit();
    });
}

function onFormSubmit(e) {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;

    // tahan submit biar overlay sempat kepaint
    e.preventDefault();

    handleSubmitWithPaint(form, e.submitter);
}

function handleLinkWithPaint(a) {
    const href = a.getAttribute("href");
    if (!href || href === "#") return;

    const scopeEl = getScopeElFromTarget(a);
    showScopeOverlay(scopeEl, "Memuat...");

    requestAnimationFrame(() => {
        window.location.href = href;
    });
}

function onPaginationClick(e) {
    const a = e.target.closest("a");
    if (!a) return;

    // hanya link pagination Laravel
    const paginationNav = a.closest('nav[role="navigation"]');
    if (!paginationNav) return;

    // jangan kalau disabled / current
    const ariaDisabled = a.getAttribute("aria-disabled");
    if (ariaDisabled === "true") return;

    // tahan navigasi biar overlay sempat kepaint
    e.preventDefault();
    handleLinkWithPaint(a);
}

export function initLoadingUX() {
    document.addEventListener("submit", onFormSubmit, true);
    document.addEventListener("click", onPaginationClick, true);
}
