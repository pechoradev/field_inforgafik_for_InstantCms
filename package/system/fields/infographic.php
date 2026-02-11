<?php

class fieldInfographic extends cmsFormField {

    public $title = 'Интерактивная инфографика';
    public $sql   = 'text';
    public $allow_index = false;
    public $var_type = 'array';

    public function getOptions() {
        return [
            new fieldList('chart_type', [
                'title'   => 'Тип диаграммы',
                'default' => 'pie',
                'items'   => [
                    'pie'   => 'Круговая диаграмма',
                    'doughnut' => 'Кольцевая диаграмма',
                    'bar'   => 'Столбчатая диаграмма',
                    'horizontalBar' => 'Горизонтальная столбчатая',
                    'line'  => 'Линейная диаграмма',
                    'radar' => 'Лепестковая диаграмма',
                    'polarArea' => 'Полярная область'
                ]
            ]),
            
            new fieldNumber('max_items', [
                'title'   => 'Максимум элементов',
                'default' => 10,
                'rules'   => [['min', 1], ['max', 20]]
            ]),
            new fieldNumber('min_items', [
                'title'   => 'Минимум элементов',
                'default' => 0,
                'rules'   => [['min', 0]],
                'hint'    => '0 - можно не заполнять'
            ]),
            new fieldCheckbox('enable_animation', [
                'title'   => 'Включить анимацию',
                'default' => 1
            ]),
            new fieldCheckbox('show_legend', [
                'title'   => 'Показывать легенду',
                'default' => 1
            ]),
            new fieldCheckbox('show_values', [
                'title'   => 'Показывать значения',
                'default' => 1
            ]),
            new fieldCheckbox('show_percents', [
                'title'   => 'Показывать проценты',
                'default' => 1
            ]),
            new fieldString('value_suffix', [
                'title'   => 'Единицы измерения',
                'default' => 'шт., руб., %',
                'hint'    => 'Перечислите через запятую'
            ]),
            new fieldList('color_scheme', [
                'title'   => 'Цветовая схема',
                'default' => 'modern',
                'items'   => [
                    'modern'    => 'Современная',
                    'pastel'    => 'Пастельная',
                    'vibrant'   => 'Яркая'
                ]
            ]),
            new fieldString('custom_colors', [
                'title'   => 'Пользовательские цвета',
                'default' => '#4e73df,#1cc88a,#36b9cc,#f6c23e,#e74a3b,#5a5c69,#858796',
                'hint'    => 'HEX коды через запятую'
            ]),
            new fieldNumber('chart_height', [
                'title'   => 'Высота диаграммы (px)',
                'default' => 350,
                'rules'   => [['min', 200], ['max', 600]]
            ]),
            new fieldCheckbox('begin_at_zero', [
                'title'   => 'Начинать шкалу с нуля',
                'default' => 1,
                'hint'    => 'Для столбчатых и линейных диаграмм'
            ]),
            new fieldCheckbox('allow_user_change_type', [
                'title'   => 'Разрешить менять тип',
                'default' => 0
            ]),
            new fieldString('add_button_text', [
                'title'   => 'Текст кнопки',
                'default' => 'Добавить элемент',
            ]),
            new fieldList('btn_position', [
                'title'   => 'Положение кнопки',
                'default' => 'top',
                'items'   => [
                    'top'    => 'Сверху',
                    'bottom' => 'Снизу',
                    'both'   => 'Сверху и снизу'
                ]
            ]),
            new fieldCheckbox('show_title', [
                'title'   => 'Показывать заголовок диаграммы',
                'default' => 1,
                'hint'    => 'Отображать название над диаграммой'
            ]),
            new fieldList('title_position', [
                'title'   => 'Положение заголовка',
                'hint'    => 'Выберите, в каком месте будет отображаться заголовок диаграммы',
                'default' => 'top',
                'items'   => [
                    'top'    => 'Сверху',
                    'bottom' => 'Снизу',
                    'left'   => 'Слева',
                    'right'  => 'Справа'
                ],
                'visible_depend' => ['options:show_title' => ['show' => ['1']]]
            ]),
            new fieldString('title_tag', [
                'title'   => 'HTML тег заголовка',
                'default' => 'h3',
                'hint'    => 'Например: h1, h2, h3, h4, div, span',
                'visible_depend' => ['options:show_title' => ['show' => ['1']]]
            ]),
        ];
    }

    public function parse($value) {
        if (!$value) { 
            return '';
        }
        
        $data = $this->prepareInputValue($value);
        
        $chart_title = isset($data['title']) ? $data['title'] : '';
        
        $items = isset($data['items']) ? $data['items'] : $data;
        
        if (!empty($chart_title)) {
            $items['_title'] = $chart_title;
        }
        
        if (empty($items) || !is_array($items)) {
            return '';
        }

        $options = $this->getDisplayOptions($data);
        $chart_data = $this->prepareChartData($items, $options);
        
        if (empty($chart_data['labels']) || empty($chart_data['values'])) {
            return '';
        }
        
        $this->includeChartJs();
        
        $chart_id = 'chart_' . $this->id . '_' . uniqid();
        
        return $this->renderChart($chart_id, $chart_data, $options);
    }

    private function prepareInputValue($value) {
        if (is_array($value)) {
            return $value;
        }
        $decoded = cmsModel::yamlToArray($value);
        return is_array($decoded) ? $decoded : [];
    }

    private function getDisplayOptions($data) {
        $options = [
            'chart_type' => $this->getOption('chart_type', 'pie'),
            'show_legend' => (bool)$this->getOption('show_legend', true),
            'show_values' => (bool)$this->getOption('show_values', true),
            'show_percents' => (bool)$this->getOption('show_percents', true),
            'value_suffix' => $this->getOption('value_suffix', 'шт., руб., %'),
            'enable_animation' => (bool)$this->getOption('enable_animation', true),
            'chart_height' => (int)$this->getOption('chart_height', 350),
            'color_scheme' => $this->getOption('color_scheme', 'modern'),
            'custom_colors' => $this->getOption('custom_colors', ''),
            'begin_at_zero' => (bool)$this->getOption('begin_at_zero', true),
            'show_title' => (bool)$this->getOption('show_title', true),
            'title_position' => $this->getOption('title_position', 'top'),
            'title_tag' => $this->getOption('title_tag', 'h3')
        ];
        
        if (!empty($data['user_chart_type'])) {
            $options['chart_type'] = $data['user_chart_type'];
        }
        
        return $options;
    }

    private function prepareChartData($items, $options) {
        $labels = [];
        $values = [];
        $suffixes = [];
        
        $suffix_list = array_map('trim', explode(',', $options['value_suffix']));
        $suffix_list = array_filter($suffix_list);
        $default_suffix = !empty($suffix_list) ? $suffix_list[0] : '';
        
        $title = '';
        if (isset($items['_title'])) {
            $title = $items['_title'];
            unset($items['_title']);
        }
        
        foreach ($items as $item) {
            if (is_array($item) && !empty($item['label']) && isset($item['value'])) {
                $labels[] = $item['label'];
                $values[] = (float)$item['value'];
                $suffixes[] = isset($item['suffix']) && !empty($item['suffix']) ? $item['suffix'] : $default_suffix;
            }
        }
        
        $colors = $this->getColorPalette(count($values), $options['color_scheme'], $options['custom_colors']);
        
        $total = array_sum($values);
        $percents = [];
        foreach ($values as $value) {
            $percents[] = $total > 0 ? round(($value / $total) * 100) : 0;
        }
        
        return [
            'labels' => $labels,
            'values' => $values,
            'suffixes' => $suffixes,
            'colors' => $colors,
            'percents' => $percents,
            'total' => $total,
            'title' => $title
        ];
    }

    private function getColorPalette($count, $scheme, $custom_colors = '') {
        $palettes = [
            'modern' => ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69', '#858796'],
            'pastel' => ['#a8e6cf', '#d4edc9', '#ffd3b6', '#ffaaa5', '#ff8b94', '#b5ead7', '#c7ceea'],
            'vibrant' => ['#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#ffeaa5', '#ff9ff3', '#feca57']
        ];
        
        if ($scheme === 'custom' && !empty($custom_colors)) {
            $custom = array_map('trim', explode(',', $custom_colors));
            $colors = [];
            for ($i = 0; $i < $count; $i++) {
                $colors[] = $custom[$i % count($custom)];
            }
            return $colors;
        }
        
        $palette = isset($palettes[$scheme]) ? $palettes[$scheme] : $palettes['modern'];
        
        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $palette[$i % count($palette)];
        }
        
        return $colors;
    }

    private function renderChart($chart_id, $data, $options) {
        $html = [];
        $height = $options['chart_height'];
        
        $title_html = '';
        if ($options['show_title'] && !empty($data['title'])) {
            $tag = $options['title_tag'];
            $position_class = 'infographic-title-' . $options['title_position'];
            
            $title_html = '<' . $tag . ' class="infographic-chart-title ' . $position_class . '">' 
                        . htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8') 
                        . '</' . $tag . '>';
        }
        
        if ($options['show_title'] && $options['title_position'] === 'top') {
            $html[] = $title_html;
        }
        
        $html[] = '<div class="infographic-wrapper" style="position: relative; height: ' . $height . 'px; width: 100%;">';
        $html[] = '<canvas id="' . $chart_id . '" style="position: absolute; left: 0; top: 0; width: 100%; height: 100%;"></canvas>';
        $html[] = '</div>';
        
        if ($options['show_title'] && $options['title_position'] === 'bottom') {
            $html[] = $title_html;
        }
        
        if ($options['show_title'] && in_array($options['title_position'], ['left', 'right'])) {
            $html = [];
            $wrapper_width = $options['title_position'] === 'left' ? '75%' : '75%';
            $title_width = '20%';
            $float = $options['title_position'] === 'left' ? 'left' : 'right';
            
            $html[] = '<div class="infographic-with-title infographic-title-' . $options['title_position'] . '" style="display: flex; align-items: center; gap: 20px;">';
            
            if ($options['title_position'] === 'left') {
                $html[] = '<div style="flex: 0 0 ' . $title_width . ';">' . $title_html . '</div>';
                $html[] = '<div style="flex: 1; position: relative; height: ' . $height . 'px;">';
                $html[] = '<canvas id="' . $chart_id . '" style="position: absolute; left: 0; top: 0; width: 100%; height: 100%;"></canvas>';
                $html[] = '</div>';
            } else {
                $html[] = '<div style="flex: 1; position: relative; height: ' . $height . 'px;">';
                $html[] = '<canvas id="' . $chart_id . '" style="position: absolute; left: 0; top: 0; width: 100%; height: 100%;"></canvas>';
                $html[] = '</div>';
                $html[] = '<div style="flex: 0 0 ' . $title_width . ';">' . $title_html . '</div>';
            }
            
            $html[] = '</div>';
        }
        
        $init_script = $this->getChartInitScript($chart_id, $data, $options);
        cmsTemplate::getInstance()->addBottom($init_script);
        
        return implode('', $html);
    }

    private function getChartInitScript($chart_id, $data, $options) {
        
        $labels = json_encode($data['labels']);
        $values = json_encode($data['values']);
        $colors = json_encode($data['colors']);
        $suffixes = json_encode($data['suffixes']);
        $percents = json_encode($data['percents']);
        
        $chart_type = $options['chart_type'];
        $show_legend = $options['show_legend'] ? 'true' : 'false';
        $show_percents = $options['show_percents'] ? 'true' : 'false';
        $enable_animation = $options['enable_animation'] ? 'true' : 'false';
        $begin_at_zero = $options['begin_at_zero'] ? 'true' : 'false';
        
        return '<script>
        (function() {
            function initChart() {
                if (typeof Chart === "undefined") {
                    setTimeout(initChart, 100);
                    return;
                }
                
                var canvas = document.getElementById("' . $chart_id . '");
                if (!canvas) {
                    setTimeout(initChart, 100);
                    return;
                }
                
                try {
                    var ctx = canvas.getContext("2d");
                    
                    var labels = ' . $labels . ';
                    var values = ' . $values . ';
                    var colors = ' . $colors . ';
                    var suffixes = ' . $suffixes . ';
                    var percents = ' . $percents . ';
                    
                    var data = {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: ' . ($chart_type === 'line' ? '"rgba(78, 115, 223, 0.1)"' : 'colors') . ',
                            borderColor: ' . ($chart_type === 'line' ? '"#4e73df"' : '"#ffffff"') . ',
                            borderWidth: ' . ($chart_type === 'line' ? '2' : '2') . ',
                            hoverBorderColor: "#ffffff",
                            hoverBorderWidth: 3,
                            pointBackgroundColor: ' . ($chart_type === 'line' ? '"#ffffff"' : 'colors') . ',
                            pointBorderColor: ' . ($chart_type === 'line' ? '"#4e73df"' : '"#ffffff"') . ',
                            pointHoverBackgroundColor: "#e74a3b",
                            pointHoverBorderColor: "#ffffff",
                            pointRadius: ' . ($chart_type === 'line' ? '4' : '3') . ',
                            pointHoverRadius: ' . ($chart_type === 'line' ? '6' : '5') . ',
                            fill: ' . ($chart_type === 'line' ? 'false' : 'false') . '
                        }]
                    };
                    
                    var options = {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            display: ' . $show_legend . ',
                            position: "top",
                            labels: {
                                boxWidth: 12,
                                fontSize: 12,
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltips: {
                            enabled: true,
                            backgroundColor: "rgba(0, 0, 0, 0.8)",
                            titleFontColor: "#ffffff",
                            bodyFontColor: "#ffffff",
                            borderColor: "#dddfeb",
                            borderWidth: 1,
                            cornerRadius: 4,
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    var dataset = data.datasets[tooltipItem.datasetIndex];
                                    var index = tooltipItem.index;
                                    var label = data.labels[index] || "";
                                    var value = dataset.data[index] || 0;
                                    var suffix = suffixes[index] || "";
                                    var formatted = value.toString().replace(/\\B(?=(\\d{3})+(?!\\d))/g, " ");
                                    
                                    if (' . $show_percents . ' && (data.datasets.length > 0)) {
                                        var percent = percents[index] || 0;
                                        return label + ": " + formatted + " " + suffix + " (" + percent + "%)";
                                    }
                                    
                                    return label + ": " + formatted + " " + suffix;
                                }
                            }
                        },
                        animation: {
                            duration: ' . ($enable_animation === 'true' ? 1000 : 0) . ',
                            easing: "easeOutQuart"
                        }
                    };
                    
                    ' . $this->getChartSpecificOptions($chart_type, $begin_at_zero) . '
                    
                    new Chart(ctx, {
                        type: "' . $chart_type . '",
                        data: data,
                        options: options
                    });
                    
                } catch(e) {
                    console.error("Chart error:", e);
                }
            }
            
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", initChart);
            } else {
                initChart();
            }
        })();
        </script>';
    }

    private function getChartSpecificOptions($chart_type, $begin_at_zero) {
    
        if ($chart_type === 'doughnut') {
            return 'options.cutoutPercentage = 60;';
        }
        
        if ($chart_type === 'polarArea') {
            return '';
        }
        
        if ($chart_type === 'radar') {
            return '
            options.scale = {
                ticks: {
                    beginAtZero: ' . $begin_at_zero . ',
                    callback: function(value) {
                        return value.toString().replace(/\\B(?=(\\d{3})+(?!\\d))/g, " ");
                    }
                }
            };
            options.elements = {
                line: {
                    tension: 0.3,
                    borderWidth: 2
                }
            };';
        }
        
        if ($chart_type === 'line') {
            return '
            options.scales = {
                yAxes: [{
                    ticks: {
                        beginAtZero: ' . $begin_at_zero . ',
                        callback: function(value) {
                            return value.toString().replace(/\\B(?=(\\d{3})+(?!\\d))/g, " ");
                        }
                    },
                    gridLines: {
                        color: "rgba(0, 0, 0, 0.05)"
                    }
                }],
                xAxes: [{
                    gridLines: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }]
            };
            
            options.showLines = true;
            options.elements = {
                line: {
                    tension: 0.3,
                    borderWidth: 2,
                    borderColor: "#4e73df",
                    backgroundColor: "rgba(78, 115, 223, 0.1)",
                    fill: false
                },
                point: {
                    radius: 4,
                    hoverRadius: 6,
                    borderWidth: 2,
                    backgroundColor: "#ffffff",
                    borderColor: "#4e73df",
                    hoverBackgroundColor: "#e74a3b",
                    hoverBorderColor: "#ffffff"
                }
            };
            
            options.datasets = {
                line: {
                    borderColor: "#4e73df",
                    backgroundColor: "rgba(78, 115, 223, 0.1)",
                    pointBackgroundColor: "#ffffff",
                    pointBorderColor: "#4e73df",
                    pointHoverBackgroundColor: "#e74a3b",
                    pointHoverBorderColor: "#ffffff"
                }
            };';
        }
        
        if ($chart_type === 'bar') {
            return '
            options.scales = {
                yAxes: [{
                    ticks: {
                        beginAtZero: ' . $begin_at_zero . ',
                        callback: function(value) {
                            return value.toString().replace(/\\B(?=(\\d{3})+(?!\\d))/g, " ");
                        }
                    },
                    gridLines: {
                        color: "rgba(0, 0, 0, 0.05)"
                    }
                }],
                xAxes: [{
                    gridLines: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }]
            };';
        }
        
        if ($chart_type === 'horizontalBar') {
            return '
            options.scales = {
                xAxes: [{
                    ticks: {
                        beginAtZero: ' . $begin_at_zero . ',
                        callback: function(value) {
                            return value.toString().replace(/\\B(?=(\\d{3})+(?!\\d))/g, " ");
                        }
                    },
                    gridLines: {
                        color: "rgba(0, 0, 0, 0.05)"
                    }
                }],
                yAxes: [{
                    gridLines: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 0,
                        minRotation: 0
                    }
                }]
            };';
        }
        
        return '';
    }

    private function getEmptyChart() {
        return '<div class="infographic-empty" style="padding: 40px; text-align: center; background: #f8f9fc; border-radius: 8px;">
            <div style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;">📊</div>
            <div style="color: #858796; font-size: 16px;">Нет данных для отображения</div>
        </div>';
    }

    private function includeChartJs() {
        static $included = false;
        
        if (!$included) {
            $template = cmsTemplate::getInstance();
            $template->addJS('templates/default/js/Chart.min.js');
            $included = true;
        }
    }

    public function getStringValue($value) {
        if (!$value) { return ''; }
        
        $data = $this->prepareInputValue($value);
        $items = isset($data['items']) ? $data['items'] : $data;
        
        if (empty($items)) { return ''; }
        
        $strings = [];
        foreach ($items as $item) {
            if (!empty($item['label']) && isset($item['value'])) {
                $str = $item['label'] . ': ' . $item['value'];
                if (!empty($item['suffix'])) {
                    $str .= ' ' . $item['suffix'];
                }
                $strings[] = $str;
            }
        }
        
        return implode(', ', $strings);
    }

    public function getInput($value) {
        $this->data['id'] = $this->id;
        $this->data['name'] = $this->element_name;
        
        $data = $this->prepareInputValue($value);
        $this->data['value'] = !empty($data) ? $data : ['items' => [], 'title' => ''];
        
        $this->data['chart_type'] = $this->getOption('chart_type', 'pie');
        $this->data['max_items'] = (int)$this->getOption('max_items', 10);
        $this->data['min_items'] = (int)$this->getOption('min_items', 0);
        $this->data['allow_user_change_type'] = (bool)$this->getOption('allow_user_change_type', false);
        $this->data['add_button_text'] = $this->getOption('add_button_text', 'Добавить элемент');
        $this->data['btn_position'] = $this->getOption('btn_position', 'top');
        $this->data['value_suffix'] = $this->getOption('value_suffix', 'шт., руб., %');
        $this->data['show_title'] = (bool)$this->getOption('show_title', true);
        $this->data['title_tag'] = $this->getOption('title_tag', 'h3');
        $this->data['title_position'] = $this->getOption('title_position', 'top');
        
        cmsTemplate::getInstance()->addCSS('templates/default/css/infographic.form.css');
        cmsTemplate::getInstance()->addJS('templates/default/js/infographic.js');
        
        return parent::getInput($this->data);
    }

    public function store($value, $is_submitted, $old_value = null) {
        if (!is_array($value)) {
            return null;
        }
        
        $items = isset($value['items']) ? $value['items'] : [];
        $title = isset($value['title']) ? trim($value['title']) : '';
        $result_items = [];
        
        foreach ($items as $index => $item) {
            if (is_array($item) && !empty($item['label']) && isset($item['value']) && $item['value'] !== '') {
                $result_item = [
                    'label' => trim($item['label']),
                    'value' => (float)str_replace(',', '.', $item['value']),
                    'ordering' => (int)$index
                ];
                
                if (isset($item['suffix']) && !empty($item['suffix'])) {
                    $result_item['suffix'] = trim($item['suffix']);
                }
                
                $result_items[] = $result_item;
            }
        }
        
        $min_items = (int)$this->getOption('min_items', 0);
        $max_items = (int)$this->getOption('max_items', 10);
        
        if (count($result_items) == 0 && empty($title)) {
            return null;
        }
        
        if ($min_items > 0 && count($result_items) < $min_items) {
            cmsUser::addSessionMessage('Необходимо добавить минимум ' . $min_items . ' элемент(ов)', 'error');
            return false;
        }
        
        if ($max_items > 0 && count($result_items) > $max_items) {
            cmsUser::addSessionMessage('Максимальное количество элементов: ' . $max_items, 'error');
            return false;
        }
        
        usort($result_items, function($a, $b) {
            return $a['ordering'] - $b['ordering'];
        });
        
        $result = [
            'items' => $result_items,
            'title' => $title
        ];
        
        if ($this->getOption('allow_user_change_type', false) && !empty($value['user_chart_type'])) {
            $result['user_chart_type'] = $value['user_chart_type'];
        }
        
        return !empty($result_items) || !empty($title) ? $result : null;
    }

    public function delete($value) {
        return true;
    }

}