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
 * Version info for the Sections learners
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_learning_analytics\local\outputs\table;
use local_learning_analytics\report_base;
use lareport_learners\query_helper;
use lareport_learners\helper;
use lareport_learners\outputs\split;
use local_learning_analytics\local\outputs\plot;

class lareport_learners extends report_base {

    private static $BAR_COLORS = [
        '#66b5ab',
        '#F26522',
        '#ffda6e',
        '#A9CF54',
        '#EA030E'
    ];

    private static function createSeries($perc, $text, $i) {
        return [
            'y' => ['lang'],
            'x' => [$perc],
            'orientation' => 'h',
            'hoverinfo' => 'none', // change to "text" to provide onhover information and make sure its not static
            'marker' => [
                'color' => (self::$BAR_COLORS[$i % count(self::$BAR_COLORS)])
            ],
            'name' => $text,
            'type' => 'bar',
            'text' => $text
        ];
    }

    private function langAndCountryPlot(int $courseid, string $type) {
        $learnersCount = query_helper::query_learners_count($courseid, 'student');

        $languages = query_helper::query_localization($courseid, $type);

        $plot = new plot();
        $langList = get_string_manager()->get_list_of_languages();

        $percSoFar = 0;
        $i = 0;
        foreach ($languages as $lang) {
            $perc = round(100 * $lang->users / $learnersCount);
            if ($perc > 3) { // otherwise it might be too small to display
                $annotations[] = [
                    'x' => ($percSoFar + ($perc / 2)),
                    'y' => 'lang',
                    'text' => $perc . '%',
                    'font' => [
                        'color' => '#fff',
                        'size' => 14,
                    ],
                    'showarrow' => false,
                    'xanchor' => 'center'
                ];
            }
            if ($type === 'lang') {
                $annotText = $langList[$lang->x];
            } else { // then its country
                if (empty($lang->x)) {
                    $annotText = 'Unknown'; // no country set
                } else {
                    $annotText = get_string($lang->x, 'countries');
                }
            }
            $annotations[] = [
                'x' => ($percSoFar + ($perc / 2)),
                'y' => 'lang',
                'yshift' => 15,
                'text' => $annotText,
                'font' => [
                    'color' => '#000',
                    'size' => 16,
                ],
                'showarrow' => false,
                'xanchor' => 'center',
                'yanchor' => 'bottom'
            ];
            $percSoFar += $perc;
            $series = self::createSeries($perc, $annotText, $i);
            $plot->add_series($series);
            $i++;
        }

        $layout = new stdClass();
        $layout->barmode = 'stack';
        $layout->annotations = $annotations;
        $layout->xaxis = ['visible' => false, 'range' => [0, 100], 'fixedrange' => true ];
        $layout->yaxis = ['visible' => false, 'fixedrange' => true];
        $layout->showlegend = false;
        $layout->margin = ['l' => 0, 'r' => 0, 't' => 10, 'b' => 0];

        $plot->set_layout($layout);
        $plot->set_height(70);
        $plot->set_static_plot(true);

        return $plot;
    }

    private function languagesAndCountries(int $courseid): array {
        $heading1 = get_string('languages_of_learners', 'lareport_learners');
        $heading2 = get_string('countries_of_learners', 'lareport_learners');

        return [
            "<h2>{$heading1}</h2>",
            $this->langAndCountryPlot($courseid, 'lang'),
            "<h2>{$heading2}</h2>",
            $this->langAndCountryPlot($courseid, 'country')
        ];
    }

    public function run(array $params): array {
        $courseid = $params['course'];

        $headingTable = get_string('most_active_learners', 'lareport_learners');

        return array_merge(
            helper::generateCourseParticipationList($courseid, 5),
            $this->languagesAndCountries($courseid)
        );
    }

    public function params(): array {
        return [
            'course' => required_param('course', PARAM_INT)
        ];
    }

}