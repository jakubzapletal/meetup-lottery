<?php

namespace MeetupLottery\Exporter;

interface ExporterInterface
{
    /**
     * @param string    $filename
     * @param array     $data
     */
    public function export($filename, $data);
} 