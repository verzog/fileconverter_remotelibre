<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace fileconverter_remotelibre;

use core_files\conversion;

/**
 * Converts documents to PDF by posting them to a remote LibreOffice service.
 *
 * @package    fileconverter_remotelibre
 * @copyright  2026 Vernon Spain
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class converter implements \core_files\converter_interface {

    /** @var string[] Source extensions this converter can turn into PDF. */
    const SUPPORTED = [
        'pptx', 'ppt', 'odp',
        'docx', 'doc', 'odt', 'rtf', 'txt',
        'xlsx', 'xls', 'ods', 'csv',
        'pdf',
    ];

    /**
     * Whether the environment can run this converter.
     *
     * @return bool True when the HTTP client Moodle needs is available.
     */
    public static function are_requirements_met() {
        return function_exists('curl_init');
    }

    /**
     * Whether this converter can convert from one format to another.
     *
     * @param string $from The source file extension.
     * @param string $to The target file extension.
     * @return bool True when the pair is supported.
     */
    public static function supports($from, $to) {
        return $to === 'pdf' && in_array(strtolower($from), self::SUPPORTED, true);
    }

    /**
     * Convert a document by posting it to the remote service and storing the PDF.
     *
     * @param conversion $conversion The conversion record to act on.
     * @return $this
     */
    public function start_document_conversion(conversion $conversion) {
        $endpoint = trim((string) get_config('fileconverter_remotelibre', 'url'));
        $apikey = (string) get_config('fileconverter_remotelibre', 'apikey');
        if ($endpoint === '' || $apikey === '') {
            $conversion->set('status', conversion::STATUS_FAILED);
            debugging('fileconverter_remotelibre is not configured (url/apikey).', DEBUG_DEVELOPER);
            return $this;
        }

        if ($conversion->get('targetformat') !== 'pdf') {
            $conversion->set('status', conversion::STATUS_FAILED);
            return $this;
        }

        $file = $conversion->get_sourcefile();
        $curl = new \curl();
        $curl->setHeader('Authorization: Bearer ' . $apikey);
        $curl->setHeader('X-Filename: ' . $file->get_filename());
        $curl->setHeader('Content-Type: application/octet-stream');
        $response = $curl->post($endpoint, $file->get_content(), [
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_TIMEOUT' => 200,
            'CURLOPT_CONNECTTIMEOUT' => 10,
        ]);

        $info = $curl->get_info();
        $httpcode = (int) ($info['http_code'] ?? 0);
        if ($curl->get_errno() || $httpcode !== 200 || substr((string) $response, 0, 5) !== '%PDF-') {
            $conversion->set('status', conversion::STATUS_FAILED);
            debugging('fileconverter_remotelibre conversion failed (HTTP ' . $httpcode . ').', DEBUG_DEVELOPER);
            return $this;
        }

        $conversion->store_destfile_from_string($response);
        $conversion->set('status', conversion::STATUS_COMPLETE);
        return $this;
    }

    /**
     * Poll an in-progress conversion. Conversions here are synchronous, so this
     * only reports the status already set by start_document_conversion().
     *
     * @param conversion $conversion The conversion record to poll.
     * @return $this
     */
    public function poll_conversion_status(conversion $conversion) {
        return $this;
    }

    /**
     * A human-readable list of the conversions this plugin supports.
     *
     * @return string The supported source formats.
     */
    public function get_supported_conversions() {
        return implode(', ', self::SUPPORTED);
    }
}
