import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.form = this.element.closest('form');
        this.modeField = this.form?.querySelector('select[name$="[horaireMode]"], select[name="horaireMode"]');

        if (!this.modeField) {
            return;
        }

        this.onModeChange = () => this.updateVisibility();
        this.modeField.addEventListener('change', this.onModeChange);
        this.updateVisibility();
    }

    disconnect() {
        this.modeField?.removeEventListener('change', this.onModeChange);
    }

    updateVisibility() {
        const dailySchedule = this.modeField.value === 'jours';

        this.setFieldVisible('horaireFixe', !dailySchedule);

        [
            'horaireLundi',
            'horaireMardi',
            'horaireMercredi',
            'horaireJeudi',
            'horaireVendredi',
            'horaireSamedi',
            'horaireDimanche',
        ].forEach((fieldName) => this.setFieldVisible(fieldName, dailySchedule));
    }

    setFieldVisible(fieldName, visible) {
        const field = this.form?.querySelector(`[name$="[${fieldName}]"], [name="${fieldName}"]`);
        const row = field?.closest('.form-group, .field-group, .field') ?? field?.parentElement;

        if (row) {
            row.hidden = !visible;
            row.setAttribute('aria-hidden', String(!visible));
        }
    }
}
