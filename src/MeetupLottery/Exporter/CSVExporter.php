<?php

namespace MeetupLottery\Exporter;

class CSVExporter implements ExporterInterface
{
    /**
     * @param string    $filename
     * @param array     $data
     */
    public function export($filename, $data)
    {
        $handle = fopen('php://memory', 'w');

        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        rewind($handle);
        fpassthru($handle);

        exit;
    }
} 