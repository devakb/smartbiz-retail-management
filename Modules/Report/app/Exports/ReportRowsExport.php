<?php

namespace Modules\Report\Exports;

use Generator;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportRowsExport implements FromGenerator, ShouldAutoSize, WithHeadings
{
    use Exportable;

    /**
     * @param  list<string>  $headings
     * @param  callable(): Generator<int, list<mixed>>  $rows
     */
    public function __construct(
        private readonly array $headings,
        private readonly mixed $rows,
    ) {}

    public function headings(): array
    {
        return $this->headings;
    }

    public function generator(): Generator
    {
        $rows = $this->rows;

        if (is_callable($rows)) {
            yield from $rows();

            return;
        }

        foreach ($rows as $row) {
            yield $row;
        }
    }
}
