import './stimulus_bootstrap.js';

const initNewsMenu = () => {
    document.querySelectorAll('[data-news-menu]').forEach((menu) => {
        if (menu.dataset.initialized === 'true') return;
        menu.dataset.initialized = 'true';
        const trigger = menu.querySelector('a');
        if (!trigger) return;

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const isOpen = menu.classList.toggle('menu-open');
            trigger.setAttribute('aria-expanded', String(isOpen));
            menu.closest('.site-header')?.classList.toggle('news-menu-open', isOpen);

            // Le focus maintient :focus-within actif ; le retirer permet au
            // second clic de refermer réellement le sous-menu.
            if (!isOpen) trigger.blur();
        });

        document.addEventListener('click', (event) => {
            if (menu.contains(event.target)) return;
            menu.classList.remove('menu-open');
            trigger.setAttribute('aria-expanded', 'false');
            menu.closest('.site-header')?.classList.remove('news-menu-open');
        });
    });
};

window.addEventListener('DOMContentLoaded', initNewsMenu);
document.addEventListener('turbo:load', initNewsMenu);

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-map-load]');
    if (!button) return;

    const container = button.closest('[data-map-embed]');
    const frame = container?.querySelector('[data-map-frame]');
    if (!container || !frame) return;

    frame.src = container.dataset.mapSrc;
    frame.classList.remove('hidden');
    button.closest('div')?.remove();
});

/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */

const refreshBoutiqueResults = async (url, push = true) => {
    const response = await fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });

    if (!response.ok) {
        throw new Error(`Erreur de filtrage (${response.status})`);
    }

    const html = await response.text();
    const documentResponse = new DOMParser().parseFromString(html, 'text/html');
    for (const selector of ['#boutique-filters', '#boutique-sort', '#boutique-results']) {
        const current = document.querySelector(selector);
        const updated = documentResponse.querySelector(selector);
        if (current && updated) current.innerHTML = updated.innerHTML;
    }

    if (push) window.history.pushState({}, '', url);
};

document.addEventListener('click', async (event) => {
    const link = event.target.closest('[data-boutique-ajax]');
    if (!link) return;

    event.preventDefault();
    try {
        await refreshBoutiqueResults(link.href);
    } catch (error) {
        console.error(error);
        window.location.href = link.href;
    }
});

const refreshPromotionResults = async (url, push = true) => {
    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!response.ok) throw new Error(`Erreur de filtrage (${response.status})`);
    const html = await response.text();
    const documentResponse = new DOMParser().parseFromString(html, 'text/html');
    for (const selector of ['#promotion-filters', '#promotion-results']) {
        const current = document.querySelector(selector);
        const updated = documentResponse.querySelector(selector);
        if (current && updated) current.innerHTML = updated.innerHTML;
    }
    if (push) window.history.pushState({}, '', url);
};

document.addEventListener('click', async (event) => {
    const link = event.target.closest('[data-promotion-ajax]');
    if (link) {
        event.preventDefault();
        try { await refreshPromotionResults(link.href); }
        catch (error) { console.error(error); window.location.href = link.href; }
    }
});

const refreshEventResults = async (url, push = true) => {
    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!response.ok) throw new Error(`Erreur de filtrage (${response.status})`);
    const html = await response.text();
    const documentResponse = new DOMParser().parseFromString(html, 'text/html');
    for (const selector of ['#event-filters', '#event-results']) {
        const current = document.querySelector(selector);
        const updated = documentResponse.querySelector(selector);
        if (current && updated) current.innerHTML = updated.innerHTML;
    }
    if (push) window.history.pushState({}, '', url);
};

const escapeCalendarHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
}[character]));

const renderEventCalendar = (calendar) => {
    const state = calendar._eventCalendarState;
    const monthStart = new Date(state.year, state.month, 1);
    const firstDay = (monthStart.getDay() + 6) % 7;
    const daysInMonth = new Date(state.year, state.month + 1, 0).getDate();
    const title = calendar.querySelector('[data-calendar-title]');
    const grid = calendar.querySelector('[data-calendar-grid]');
    const events = state.events;

    title.textContent = new Intl.DateTimeFormat('fr-FR', {month: 'long', year: 'numeric'}).format(monthStart);
    grid.replaceChildren();

    for (let index = 0; index < firstDay; index += 1) {
        grid.insertAdjacentHTML('beforeend', '<div class="min-h-[110px] border-b border-r border-slate-100 bg-slate-50/50"></div>');
    }

    for (let day = 1; day <= daysInMonth; day += 1) {
        const dayEvents = events.filter((event) => {
            const date = new Date(event.start);
            return date.getFullYear() === state.year && date.getMonth() === state.month && date.getDate() === day;
        });
        const eventMarkup = dayEvents.map((event) => {
            const date = new Date(event.start);
            const time = date.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
            return `<a href="${escapeCalendarHtml(event.href)}" class="mt-2 block rounded-lg bg-brand-900 px-2 py-1.5 text-left text-xs text-white transition hover:bg-brand-800"><span class="font-semibold">${time}</span> ${escapeCalendarHtml(event.title)}${event.location ? `<span class="mt-0.5 block truncate text-white/70">${escapeCalendarHtml(event.location)}</span>` : ''}</a>`;
        }).join('');
        grid.insertAdjacentHTML('beforeend', `<div class="min-h-[110px] border-b border-r border-slate-100 p-2 align-top"><span class="text-xs font-semibold text-olympia-muted">${day}</span>${eventMarkup}</div>`);
    }

    const totalCells = firstDay + daysInMonth;
    for (let index = totalCells; index < Math.ceil(totalCells / 7) * 7; index += 1) {
        grid.insertAdjacentHTML('beforeend', '<div class="min-h-[110px] border-b border-r border-slate-100 bg-slate-50/50"></div>');
    }
};

const initEventCalendars = () => {
    document.querySelectorAll('[data-event-calendar]').forEach((calendar) => {
        if (calendar._eventCalendarState) return;
        const events = JSON.parse(calendar.dataset.events || '[]');
        const firstEventDate = events[0]?.start ? new Date(events[0].start) : new Date();
        calendar._eventCalendarState = {events, year: firstEventDate.getFullYear(), month: firstEventDate.getMonth()};
    });
};

document.addEventListener('click', (event) => {
    const viewButton = event.target.closest('[data-event-view]');
    if (viewButton) {
        const results = viewButton.closest('#event-results');
        if (!results) return;
        const isCalendar = viewButton.dataset.eventView === 'calendar';
        results.querySelector('#event-list-view')?.classList.toggle('hidden', isCalendar);
        results.querySelector('#event-calendar-view')?.classList.toggle('hidden', !isCalendar);
        results.querySelectorAll('[data-event-view]').forEach((button) => {
            button.classList.toggle('bg-olympia', button === viewButton);
            button.classList.toggle('text-white', button === viewButton);
            button.classList.toggle('text-olympia-muted', button !== viewButton);
        });
        if (isCalendar) {
            initEventCalendars();
            renderEventCalendar(results.querySelector('[data-event-calendar]'));
        }
        return;
    }

    const calendarButton = event.target.closest('[data-calendar-prev], [data-calendar-next]');
    if (!calendarButton) return;
    const calendar = calendarButton.closest('[data-event-calendar]');
    if (!calendar?._eventCalendarState) return;
    calendar._eventCalendarState.month += calendarButton.hasAttribute('data-calendar-next') ? 1 : -1;
    if (calendar._eventCalendarState.month < 0) { calendar._eventCalendarState.month = 11; calendar._eventCalendarState.year -= 1; }
    if (calendar._eventCalendarState.month > 11) { calendar._eventCalendarState.month = 0; calendar._eventCalendarState.year += 1; }
    renderEventCalendar(calendar);
});

document.addEventListener('turbo:load', initEventCalendars);
window.addEventListener('DOMContentLoaded', initEventCalendars);

document.addEventListener('click', async (event) => {
    const link = event.target.closest('[data-event-ajax]');
    if (!link) return;
    event.preventDefault();
    try { await refreshEventResults(link.href); }
    catch (error) { console.error(error); window.location.href = link.href; }
});

const newsletterModal = document.querySelector('#newsletter-modal');
document.addEventListener('click', (event) => {
    if (event.target.closest('[data-newsletter-open]')) {
        newsletterModal?.classList.remove('hidden');
        newsletterModal?.classList.add('flex');
        newsletterModal?.querySelector('input')?.focus();
    }
    if (event.target.closest('[data-newsletter-close]') || event.target === newsletterModal) {
        newsletterModal?.classList.add('hidden');
        newsletterModal?.classList.remove('flex');
    }
});

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('[data-newsletter-form]');
    if (!form) return;
    event.preventDefault();
    const message = form.querySelector('[data-newsletter-message]');
    const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await response.json();
    message.textContent = data.message;
    message.className = `text-sm ${data.success ? 'text-green-700' : 'text-red-600'}`;
    if (data.success) form.reset();
});

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('[data-contact-form]');
    if (!form) return;
    event.preventDefault();
    const message = form.querySelector('[data-contact-message]');
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await response.json();
        message.textContent = data.message;
        message.className = `text-sm ${data.success ? 'text-green-700' : 'text-red-600'}`;
        if (data.success) form.reset();
    } catch (error) {
        console.error(error);
        message.textContent = 'Une erreur est survenue. Veuillez réessayer.';
        message.className = 'text-sm text-red-600';
    }
});

window.addEventListener('popstate', () => refreshBoutiqueResults(window.location.href, false));

const initHomeCarousels = () => {
    document.querySelectorAll('[data-home-carousel]').forEach((carousel) => {
        if (carousel.dataset.carouselInitialized === 'true') return;

        const viewport = carousel.querySelector('.home-carousel-viewport');
        const track = carousel.querySelector('.home-carousel-track');
        const dots = carousel.querySelector('[data-carousel-dots]');
        if (!viewport || !track || !dots || track.children.length === 0) return;

        carousel.dataset.carouselInitialized = 'true';

        const originalCards = [...track.children];
        if (originalCards.length > 1) {
            originalCards.forEach((card) => {
                const clone = card.cloneNode(true);
                clone.setAttribute('aria-hidden', 'true');
                track.append(clone);
            });
            track.classList.add('is-marquee');
            dots.hidden = true;
            return;
        }

        let pageCount = 0;
        let activePage = 0;
        let timer;

        const updateDots = () => {
            const card = track.firstElementChild;
            const styles = window.getComputedStyle(track);
            const gap = Number.parseFloat(styles.columnGap || styles.gap || '0');
            const step = (card ? card.getBoundingClientRect().width : viewport.clientWidth) + gap;
            activePage = Math.min(pageCount - 1, Math.round(track.scrollLeft / Math.max(1, step)));
            dots.querySelectorAll('span').forEach((dot, index) => {
                dot.setAttribute('aria-current', index === activePage ? 'true' : 'false');
            });
        };

        const renderDots = () => {
            pageCount = Math.max(1, track.children.length);
            dots.replaceChildren();
            for (let index = 0; index < pageCount; index += 1) {
                const dot = document.createElement('span');
                dot.setAttribute('aria-hidden', 'true');
                dots.append(dot);
            }
            updateDots();
        };

        const getCardStep = () => {
            const card = track.firstElementChild;
            if (!card) return viewport.clientWidth;
            const styles = window.getComputedStyle(track);
            const gap = Number.parseFloat(styles.columnGap || styles.gap || '0');
            return card.getBoundingClientRect().width + gap;
        };

        const stopAutoScroll = () => window.clearInterval(timer);
        const scrollToPage = (page) => {
            const step = getCardStep();
            const target = page * step;
            const maxScroll = track.scrollWidth - viewport.clientWidth;
            track.scrollTo({
                left: target > maxScroll ? 0 : target,
                behavior: 'smooth',
            });
        };

        const startAutoScroll = () => {
            stopAutoScroll();
            if (pageCount < 2) return;
            timer = window.setInterval(() => {
                const nextPage = (activePage + 1) % pageCount;
                scrollToPage(nextPage);
            }, 3000);
        };

        track.addEventListener('scroll', updateDots, { passive: true });
        window.addEventListener('resize', () => { renderDots(); startAutoScroll(); });

        renderDots();
        startAutoScroll();
    });
};

window.addEventListener('DOMContentLoaded', initHomeCarousels);
document.addEventListener('turbo:load', initHomeCarousels);

const refreshRestaurantResults = async (url, push = true) => {
    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!response.ok) throw new Error(`Erreur de filtrage (${response.status})`);

    const html = await response.text();
    const documentResponse = new DOMParser().parseFromString(html, 'text/html');
    for (const selector of ['#restaurant-filters', '#restaurant-results']) {
        const current = document.querySelector(selector);
        const updated = documentResponse.querySelector(selector);
        if (current && updated) current.innerHTML = updated.innerHTML;
    }
    if (push) window.history.pushState({}, '', url);
};

document.addEventListener('click', async (event) => {
    const link = event.target.closest('[data-restaurant-ajax]');
    if (!link) return;

    event.preventDefault();
    try {
        await refreshRestaurantResults(link.href);
    } catch (error) {
        console.error(error);
        window.location.href = link.href;
    }
});

