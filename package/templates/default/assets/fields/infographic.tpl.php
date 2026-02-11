<?php

$this->addTplCSSName('infographic.form');
$this->addTplJSName('infographic');

$id = $field->id;
$element_name = $field->element_name;
$value = $field->data['value'] ?? ['items' => [], 'title' => ''];
$items = isset($value['items']) && is_array($value['items']) ? $value['items'] : [];
$chart_title = isset($value['title']) ? $value['title'] : '';
$chart_type = $field->data['chart_type'] ?? 'pie';
$max_items = (int)($field->data['max_items'] ?? 10);
$min_items = (int)($field->data['min_items'] ?? 0);
$allow_user_change_type = (bool)($field->data['allow_user_change_type'] ?? false);
$add_button_text = $field->data['add_button_text'] ?? 'Добавить элемент';
$btn_position = $field->data['btn_position'] ?? 'top';
$value_suffix = $field->data['value_suffix'] ?? 'шт., руб., %';
$show_title = (bool)($field->data['show_title'] ?? true);
$title_tag = $field->data['title_tag'] ?? 'h3';
$title_position = $field->data['title_position'] ?? 'top';
$suffix_options = array_map('trim', explode(',', $value_suffix));
$suffix_options = array_filter($suffix_options);
$current_count = count($items);
$user_chart_type = isset($value['user_chart_type']) ? $value['user_chart_type'] : $chart_type;

$chart_types = [
    'pie' => 'Круговая',
    'doughnut' => 'Кольцевая',
    'bar' => 'Столбчатая',
    'horizontalBar' => 'Горизонтальная',
    'line' => 'Линейная',
    'radar' => 'Лепестковая',
    'polarArea' => 'Полярная'
];

$color_palette = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69', '#858796'];
?>

<?php if ($field->title) { ?>
    <label for="<?php echo $field->id; ?>"><?php echo $field->title; ?></label>
<?php } ?>

<div id="infographic_<?php echo $id; ?>" class="infographic-field">
    
    <?php if ($show_title) { ?>
    <div class="infographic-title-field mb-3">
        <label class="form-label">Заголовок диаграммы:</label>
        <input type="text" 
               name="<?php echo $element_name; ?>[title]" 
               value="<?php echo htmlspecialchars($chart_title); ?>" 
               class="form-control chart-title-input" 
               placeholder="Например: Уровень бедности по странам, 2025 год">
        <small class="form-text text-muted">Будет отображаться над диаграммой</small>
    </div>
    <?php } ?>
    
    <?php if ($allow_user_change_type) { ?>
    <div class="infographic-type-selector mb-3">
        <label class="form-label">Тип диаграммы:</label>
        <div class="infographic-type-buttons">
            <?php foreach ($chart_types as $type_key => $type_name) { ?>
                <label class="infographic-type-btn <?php echo $user_chart_type === $type_key ? 'active' : ''; ?>">
                    <input type="radio" 
                           name="<?php echo $element_name; ?>[user_chart_type]" 
                           value="<?php echo $type_key; ?>" 
                           <?php echo $user_chart_type === $type_key ? 'checked' : ''; ?>
                           style="display: none;">
                    <span class="type-icon">
                        <?php 
                        $icons = ['pie' => '🥧','doughnut' => '🍩','bar' => '📊','horizontalBar' => '📈','line' => '📉','radar' => '🕸️','polarArea' => '🎯'];
                        echo $icons[$type_key] ?? '📊';
                        ?>
                    </span>
                    <span class="type-name"><?php echo $type_name; ?></span>
                </label>
            <?php } ?>
        </div>
    </div>
    <?php } else { ?>
        <input type="hidden" name="<?php echo $element_name; ?>[user_chart_type]" value="<?php echo $chart_type; ?>">
    <?php } ?>
    
    <?php if (in_array($btn_position, ['top', 'both'])) { ?>
    <div class="infographic-toolbar infographic-toolbar-top">
        <button type="button" class="btn btn-primary btn-sm add-item" onclick="icms.infographic.addItem('<?php echo $id; ?>')">
            <?php echo html_svg_icon('solid', 'plus'); ?>
            <?php echo htmlspecialchars($add_button_text); ?>
        </button>
        
        <div class="infographic-stats">
            <span class="items-count">
                Добавлено: <span id="items_count_<?php echo $id; ?>"><?php echo $current_count; ?> из <?php echo $max_items > 0 ? $max_items : '∞'; ?></span>
            </span>
            <?php if ($min_items > 0) { ?>
                <span class="min-items-hint">(минимум: <?php echo $min_items; ?>)</span>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <div class="infographic-items" id="infographic_items_<?php echo $id; ?>">
        <?php if (!empty($items)) { ?>
            <?php foreach ($items as $index => $item) { ?>
                <?php if (!empty($item['label']) && isset($item['value'])) { 
                    $color_index = $index % count($color_palette);
                    $bg_color = $color_palette[$color_index];
                    ?>
                    <div class="infographic-item" data-index="<?php echo $index; ?>">
                        <div class="item-sort">
                            <span class="sort-handle">
                                <?php echo html_svg_icon('solid', 'arrows-alt-v'); ?>
                            </span>
                        </div>
                        
                        <div class="item-content">
                            <div class="form-row align-items-center">
                                <div class="col-md-5">
                                    <input type="text" name="<?php echo $element_name; ?>[items][<?php echo $index; ?>][label]" value="<?php echo htmlspecialchars($item['label']); ?>" class="form-control label-input" placeholder="Название" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="number" name="<?php echo $element_name; ?>[items][<?php echo $index; ?>][value]" value="<?php echo htmlspecialchars($item['value']); ?>" class="form-control value-input" min="0" step="any"placeholder="Значение" required>
                                        
                                        <?php if (!empty($suffix_options)) { ?>
                                            <div class="input-group-append">
                                                <?php if (count($suffix_options) == 1) { ?>
                                                    <span class="input-group-text"><?php echo htmlspecialchars($suffix_options[0]); ?></span>
                                                    <input type="hidden" name="<?php echo $element_name; ?>[items][<?php echo $index; ?>][suffix]" value="<?php echo htmlspecialchars($suffix_options[0]); ?>">
                                                <?php } else { ?>
                                                    <select name="<?php echo $element_name; ?>[items][<?php echo $index; ?>][suffix]" class="custom-select suffix-select">
                                                        <option value="">Выберите</option>
                                                        <?php foreach ($suffix_options as $suffix) { ?>
                                                            <option value="<?php echo htmlspecialchars($suffix); ?>" 
                                                                <?php if (!empty($item['suffix']) && $item['suffix'] == $suffix) echo 'selected'; ?>>
                                                                <?php echo htmlspecialchars($suffix); ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="item-color-preview" style="background-color: <?php echo $bg_color; ?>;">
                                        <span class="color-value"><?php echo $bg_color; ?></span>
                                    </div>
                                </div>
                                
                                <div class="col-md-1">
                                    <div class="item-actions">
                                        <button type="button" class="btn btn-danger btn-sm remove-item" onclick="icms.infographic.removeItem(this)">
                                            <?php echo html_svg_icon('solid', 'trash'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" name="<?php echo $element_name; ?>[items][<?php echo $index; ?>][ordering]" value="<?php echo $index; ?>" class="item-order">
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </div>

    <?php if (in_array($btn_position, ['bottom', 'both'])) { ?>
    <div class="infographic-toolbar infographic-toolbar-bottom mt-3">
        <button type="button" class="btn btn-primary btn-sm add-item" onclick="icms.infographic.addItem('<?php echo $id; ?>')">
            <?php echo html_svg_icon('solid', 'plus'); ?>
            <?php echo htmlspecialchars($add_button_text); ?>
        </button>
    </div>
    <?php } ?>

    <div class="infographic-hint">
        <small class="text-muted">
            <?php if ($max_items > 0) { ?>
                Максимум элементов: <?php echo $max_items; ?>
            <?php } else { ?>
                Максимум элементов: без ограничений
            <?php } ?>
            
            <?php if ($min_items > 0) { ?>
                , минимум: <?php echo $min_items; ?>
            <?php } else { ?>
                , минимум: не требуется
            <?php } ?>
        </small>
    </div>

    <div id="infographic_values_<?php echo $id; ?>" class="infographic-values-container mt-4" style="display: none;">
        <div class="infographic-values-header">
            <span class="infographic-values-title">Значения:</span>
            <span class="infographic-values-total"></span>
        </div>
        <div class="infographic-values-list" id="infographic_values_list_<?php echo $id; ?>"></div>
    </div>

</div>

<?php ob_start(); ?>
<script>
$(function(){
    icms.infographic.init('<?php echo $id; ?>', {
        element_name: '<?php echo $element_name; ?>',
        max_items: <?php echo $max_items; ?>,
        min_items: <?php echo $min_items; ?>,
        add_button_text: '<?php echo htmlspecialchars($add_button_text); ?>',
        suffix_options: <?php echo json_encode($suffix_options); ?>,
        chart_type: '<?php echo $chart_type; ?>',
        allow_user_change_type: <?php echo $allow_user_change_type ? 'true' : 'false'; ?>,
        color_palette: <?php echo json_encode($color_palette); ?>,
        show_title: <?php echo $show_title ? 'true' : 'false'; ?>,
        title_tag: '<?php echo $title_tag; ?>',
        title_position: '<?php echo $title_position; ?>'
    });
});
</script>
<?php $this->addBottom(ob_get_clean()); ?>