<?php

namespace App\Entity;

trait HorairesFormDataTrait
{
    private ?string $horaireMode = null;
    private ?string $horaireFixe = null;
    private array $horairesJours = [];
    private const JOURS = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

    public function getHoraireMode(): string { return $this->horaireMode ?? ($this->horaires['mode'] ?? 'fixe'); }
    public function setHoraireMode(?string $value): static { $this->horaireMode = $value; return $this; }
    public function getHoraireFixe(): ?string { return $this->horaireFixe ?? ($this->horaires['fixe'] ?? null); }
    public function setHoraireFixe(?string $value): static { $this->horaireFixe = $value; return $this; }

    public function getHorairesFormData(): ?array
    {
        if ($this->getHoraireMode() === 'fixe') return ['mode' => 'fixe', 'fixe' => $this->getHoraireFixe()];
        $jours = [];
        foreach (self::JOURS as $jour) $jours[$jour] = $this->getHoraireJour($jour);
        return ['mode' => 'jours', 'jours' => $jours];
    }

    public function getHorairesAffichage(): ?string
    {
        $data = $this->horaires;
        if (!$data) return null;
        if (($data['mode'] ?? 'fixe') === 'fixe') return $data['fixe'] ?? null;

        $labels = ['lundi' => 'Lun', 'mardi' => 'Mar', 'mercredi' => 'Mer', 'jeudi' => 'Jeu', 'vendredi' => 'Ven', 'samedi' => 'Sam', 'dimanche' => 'Dim'];
        $items = [];
        $horaireCourant = null;
        $jourDebut = null;
        $jourFin = null;

        foreach ($labels as $jour => $label) {
            $horaire = trim((string) ($data['jours'][$jour] ?? ''));
            if ($horaire === '') {
                if ($horaireCourant !== null) {
                    $items[] = $this->formatHoraireGroupe($jourDebut, $jourFin, $horaireCourant);
                    $horaireCourant = $jourDebut = $jourFin = null;
                }
                continue;
            }
            if ($horaireCourant === $horaire) {
                $jourFin = $label;
                continue;
            }
            if ($horaireCourant !== null) $items[] = $this->formatHoraireGroupe($jourDebut, $jourFin, $horaireCourant);
            $horaireCourant = $horaire;
            $jourDebut = $label;
            $jourFin = $label;
        }
        if ($horaireCourant !== null) $items[] = $this->formatHoraireGroupe($jourDebut, $jourFin, $horaireCourant);

        return $items ? implode("\n", $items) : null;
    }

    private function formatHoraireGroupe(string $jourDebut, string $jourFin, string $horaire): string
    {
        return ($jourDebut === $jourFin ? $jourDebut : $jourDebut.' - '.$jourFin).': '.$horaire;
    }

    private function getHoraireJour(string $jour): ?string { return $this->horairesJours[$jour] ?? ($this->horaires['jours'][$jour] ?? null); }
    public function setHoraireLundi(?string $value): static { $this->horairesJours['lundi'] = $value; return $this; }
    public function setHoraireMardi(?string $value): static { $this->horairesJours['mardi'] = $value; return $this; }
    public function setHoraireMercredi(?string $value): static { $this->horairesJours['mercredi'] = $value; return $this; }
    public function setHoraireJeudi(?string $value): static { $this->horairesJours['jeudi'] = $value; return $this; }
    public function setHoraireVendredi(?string $value): static { $this->horairesJours['vendredi'] = $value; return $this; }
    public function setHoraireSamedi(?string $value): static { $this->horairesJours['samedi'] = $value; return $this; }
    public function setHoraireDimanche(?string $value): static { $this->horairesJours['dimanche'] = $value; return $this; }
    public function getHoraireLundi(): ?string { return $this->getHoraireJour('lundi'); }
    public function getHoraireMardi(): ?string { return $this->getHoraireJour('mardi'); }
    public function getHoraireMercredi(): ?string { return $this->getHoraireJour('mercredi'); }
    public function getHoraireJeudi(): ?string { return $this->getHoraireJour('jeudi'); }
    public function getHoraireVendredi(): ?string { return $this->getHoraireJour('vendredi'); }
    public function getHoraireSamedi(): ?string { return $this->getHoraireJour('samedi'); }
    public function getHoraireDimanche(): ?string { return $this->getHoraireJour('dimanche'); }
}
