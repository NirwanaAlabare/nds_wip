<?php

namespace App\Exports\MgtReportDashboard;

use Maatwebsite\Excel\Events\AfterSheet;

/**
 * View export memakai <title> di blade-nya, dan HTML reader memakai itu sebagai
 * nama sheet — jadi nama sheet dipaksa ulang setelah sheet selesai dibuat.
 */
trait ForcesSheetTitle
{
    public function registerEvents(): array
    {
        $events      = method_exists(get_parent_class($this), 'registerEvents') ? parent::registerEvents() : [];
        $parentAfter = $events[AfterSheet::class] ?? null;

        $events[AfterSheet::class] = function (AfterSheet $event) use ($parentAfter) {
            if ($parentAfter) {
                $parentAfter($event);
            }

            $event->sheet->getDelegate()->setTitle($this->title());
        };

        return $events;
    }
}
