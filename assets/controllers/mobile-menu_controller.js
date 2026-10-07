import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['panel', 'open', 'close'];

    toggle() {
        const isHidden = this.panelTarget.classList.toggle('hidden');
        if (this.hasOpenTarget) this.openTarget.classList.toggle('hidden', !isHidden);
        if (this.hasCloseTarget) this.closeTarget.classList.toggle('hidden', isHidden);
        this.element.querySelector('button[aria-expanded]')
            ?.setAttribute('aria-expanded', String(!isHidden));
    }

    closeOnLink(event) {
        if (!event.target.closest('a')) return;

        this.close();
    }

    close() {
        this.panelTarget.classList.add('hidden');
        if (this.hasOpenTarget) this.openTarget.classList.remove('hidden');
        if (this.hasCloseTarget) this.closeTarget.classList.add('hidden');
        this.element.querySelector('button[aria-expanded]')
            ?.setAttribute('aria-expanded', 'false');
    }
}
