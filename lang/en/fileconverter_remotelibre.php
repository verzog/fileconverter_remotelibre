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

/**
 * Strings for the remote LibreOffice document converter.
 *
 * @package    fileconverter_remotelibre
 * @copyright  2026 Vernon Spain
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['apikey'] = 'API key';
$string['apikey_desc'] = 'The bearer token the render service expects in the Authorization header.';
$string['pluginname'] = 'Remote LibreOffice';
$string['privacy:metadata:remotelibre'] = 'To convert documents to PDF, file contents are sent to the configured remote render service.';
$string['privacy:metadata:remotelibre:file'] = 'The document\'s content is sent to the remote render service for conversion. The real filename is not sent -- only a content hash and the file extension -- and no user identity is transmitted.';
$string['url'] = 'Convert endpoint URL';
$string['url_desc'] = 'The full HTTPS URL of the render service\'s convert endpoint, for example https://render.example.org/render/convert. Plain HTTP is rejected, since the API key and document contents are sent to it.';
