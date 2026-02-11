var icms = icms || {};

icms.infographic = (function ($) {

    this.instances = {};
    this.item_counter = 0;

    this.init = function(field_id, options) {
        this.instances[field_id] = options;

        var existing_items = $('#infographic_items_' + field_id + ' .infographic-item');
        this.item_counter = existing_items.length;
        
        this.updateStats(field_id);
        this.initSorting(field_id);
        this.initTypeSelector(field_id);
        this.initLivePreview(field_id);
    };

    this.initSorting = function(field_id) {
        var self = this;
        var container = $('#infographic_items_' + field_id);
        
        if (container.length > 0) {
            container.sortable({
                handle: '.sort-handle',
                items: '.infographic-item',
                placeholder: 'infographic-item item-placeholder',
                tolerance: 'pointer',
                cursor: 'move',
                opacity: 0.8,
                distance: 10,
                start: function(e, ui) {
                    ui.item.addClass('dragging');
                },
                stop: function(e, ui) {
                    ui.item.removeClass('dragging');
                    self.updateItemOrders(field_id);
                    self.updateLivePreview(field_id);
                }
            });
        }
    };

    this.updateItemOrders = function(field_id) {
        var options = this.instances[field_id];
        var color_palette = options.color_palette || ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69', '#858796'];
        
        $('#infographic_items_' + field_id + ' .infographic-item').each(function(index) {
            $(this).attr('data-index', index);
            $(this).find('.item-order').val(index);
            
            var field_prefix = options.element_name + '[items][' + index + ']';
            $(this).find('.label-input').attr('name', field_prefix + '[label]');
            $(this).find('.value-input').attr('name', field_prefix + '[value]');
            $(this).find('.item-order').attr('name', field_prefix + '[ordering]');
            
            var suffix_select = $(this).find('.suffix-select');
            if (suffix_select.length) {
                suffix_select.attr('name', field_prefix + '[suffix]');
            }
            
            var color = color_palette[index % color_palette.length];
            $(this).find('.item-color-preview').css('background-color', color);
            $(this).find('.color-value').text(color);
        });
    };

    this.addItem = function(field_id) {
        var options = this.instances[field_id];
        
        if (options.max_items > 0 && this.item_counter >= options.max_items) {
            this.showNotification('Достигнуто максимальное количество: ' + options.max_items, 'warning');
            return;
        }
        
        var current_items = $('#infographic_items_' + field_id + ' .infographic-item');
        var index = current_items.length;
        
        var item_html = this.getItemHtml(field_id, index);
        var newItem = $(item_html).hide();
        
        $('#infographic_items_' + field_id).append(newItem);
        
        newItem.slideDown(300, function() {
            $(this).css('opacity', '1');
            $(this).find('.label-input').focus();
        });
        
        this.item_counter = index + 1;
        this.updateStats(field_id);
        
        newItem.addClass('item-added');
        setTimeout(function() {
            newItem.removeClass('item-added');
        }, 500);
        
        this.updateLivePreview(field_id);
    };

    this.getItemHtml = function(field_id, index) {
        var options = this.instances[field_id];
        var field_prefix = options.element_name + '[items][' + index + ']';
        
        var color_palette = options.color_palette || ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69', '#858796'];
        var color = color_palette[index % color_palette.length];
        
        var suffix_html = '';
        if (options.suffix_options && options.suffix_options.length > 0) {
            suffix_html += '<div class="input-group-append">';
            
            if (options.suffix_options.length === 1) {
                suffix_html += '<span class="input-group-text">' + 
                    this.escapeHtml(options.suffix_options[0]) + '</span>' +
                    '<input type="hidden" name="' + field_prefix + '[suffix]" value="' + 
                    this.escapeHtml(options.suffix_options[0]) + '">';
            } else {
                suffix_html += '<select name="' + field_prefix + '[suffix]" class="custom-select suffix-select">';
                suffix_html += '<option value="">Выберите</option>';
                for (var i = 0; i < options.suffix_options.length; i++) {
                    suffix_html += '<option value="' + this.escapeHtml(options.suffix_options[i]) + '">' + 
                        this.escapeHtml(options.suffix_options[i]) + '</option>';
                }
                suffix_html += '</select>';
            }
            
            suffix_html += '</div>';
        }
        
        var html = '<div class="infographic-item" data-index="' + index + '" style="opacity: 0;">' +
            '<div class="item-sort">' +
            '<span class="sort-handle">' +
            '<svg class="icms-svg-icon w-16" fill="currentColor">' +
            '<use href="/templates/modern/images/icons/solid.svg#arrows-alt-v"></use>' +
            '</svg>' +
            '</span>' +
            '</div>' +
            
            '<div class="item-content">' +
            '<div class="form-row align-items-center">' +
            '<div class="col-md-5">' +
            '<input type="text" ' +
            'name="' + field_prefix + '[label]" ' +
            'class="form-control label-input" ' +
            'placeholder="Название" ' +
            'required>' +
            '</div>' +
            
            '<div class="col-md-4">' +
            '<div class="input-group">' +
            '<input type="number" ' +
            'name="' + field_prefix + '[value]" ' +
            'class="form-control value-input" ' +
            'min="0" step="any" ' +
            'placeholder="Значение" ' +
            'required>' +
            suffix_html +
            '</div>' +
            '</div>' +
            
            '<div class="col-md-2">' +
            '<div class="item-color-preview" style="background-color: ' + color + ';">' +
            '<span class="color-value">' + color + '</span>' +
            '</div>' +
            '</div>' +
            
            '<div class="col-md-1">' +
            '<div class="item-actions">' +
            '<button type="button" class="btn btn-danger btn-sm remove-item" onclick="icms.infographic.removeItem(this)">' +
            '<svg class="icms-svg-icon w-16" fill="currentColor">' +
            '<use href="/templates/modern/images/icons/solid.svg#trash"></use>' +
            '</svg>' +
            '</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            
            '<input type="hidden" name="' + field_prefix + '[ordering]" value="' + index + '" class="item-order">' +
            '</div>' +
            '</div>';
        
        return html;
    };

    this.removeItem = function(button) {
        var item = $(button).closest('.infographic-item');
        var field_id = item.closest('.infographic-field').attr('id').replace('infographic_', '');
        var options = this.instances[field_id];
        var remaining_count = this.item_counter - 1;

        if (options.min_items > 0 && remaining_count < options.min_items) {
            this.showNotification('Нельзя удалить. Минимальное количество: ' + options.min_items, 'warning');
            return;
        }

        var self = this;
        
        item.slideUp(300, function() {
            $(this).remove();
            self.item_counter--;
            self.updateStats(field_id);
            self.updateItemOrders(field_id);
            self.updateLivePreview(field_id);
        });
    };

    this.updateStats = function(field_id) {
        var count = $('#infographic_items_' + field_id + ' .infographic-item').length;
        var options = this.instances[field_id];
        
        var max_text = options.max_items > 0 ? options.max_items : '∞';
        $('#items_count_' + field_id).text(count + ' из ' + max_text);
        
        var addButton = $('#infographic_' + field_id + ' .add-item');
        if (options.max_items > 0 && count >= options.max_items) {
            addButton.prop('disabled', true).addClass('disabled');
        } else {
            addButton.prop('disabled', false).removeClass('disabled');
        }
        
        var minHint = $('#infographic_' + field_id + ' .min-items-hint');
        if (minHint.length) {
            if (count < options.min_items) {
                minHint.css('color', '#e74a3b').css('font-weight', 'bold');
            } else {
                minHint.css('color', '').css('font-weight', '');
            }
        }
    };

    this.canRemove = function(field_id) {
        var options = this.instances[field_id];
        var current_count = $('#infographic_items_' + field_id + ' .infographic-item').length;
        
        if (options.min_items === 0) {
            return true;
        }
        
        return (current_count - 1) >= options.min_items;
    };

    this.initTypeSelector = function(field_id) {
        var self = this;
        
        $('#infographic_' + field_id + ' .infographic-type-btn').on('click', function() {
            var btn = $(this);
            var container = btn.closest('.infographic-type-buttons');
            
            container.find('.infographic-type-btn').removeClass('active');
            btn.addClass('active');
            btn.find('input[type="radio"]').prop('checked', true);
            
            self.updateLivePreview(field_id);
        });
    };

    this.initLivePreview = function(field_id) {
        var self = this;
        var container = $('#infographic_items_' + field_id);
        
        if ($('#infographic_' + field_id + ' .infographic-live-preview').length === 0) {
            $('#infographic_' + field_id).append(
                '<div class="infographic-live-preview mt-4">' +
                '<h6>Предпросмотр диаграммы</h6>' +
                '<div class="preview-container" style="height: 200px; position: relative;">' +
                '<canvas id="preview_' + field_id + '" style="width: 100%; height: 100%;"></canvas>' +
                '</div>' +
                '</div>'
            );
        }
        
        container.on('input', '.label-input, .value-input, .suffix-select', function() {
            self.updateLivePreview(field_id);
        });
        
        setTimeout(function() {
            self.updateLivePreview(field_id);
        }, 500);
    };

    this.updateLivePreview = function(field_id) {
        var self = this;
        var options = this.instances[field_id];
        var items = [];
        
        $('#infographic_items_' + field_id + ' .infographic-item').each(function() {
            var label = $(this).find('.label-input').val();
            var value = $(this).find('.value-input').val();
            var suffix = $(this).find('.suffix-select').val() || '';
            
            if (label && value && parseFloat(value) > 0) {
                items.push({
                    label: label,
                    value: parseFloat(value),
                    suffix: suffix
                });
            }
        });
        
        if (items.length === 0) {
            $('#infographic_' + field_id + ' .infographic-live-preview').hide();
            return;
        }
        
        $('#infographic_' + field_id + ' .infographic-live-preview').show();
        
        var chart_type = options.chart_type;
        var selected_type = $('#infographic_' + field_id + ' input[name$="[user_chart_type]"]:checked').val();
        
        if (selected_type) {
            chart_type = selected_type;
        }
        
        if (typeof Chart === 'undefined') {
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js';
            script.onload = function() {
                setTimeout(function() {
                    self.renderPreview(field_id, items, chart_type);
                }, 200);
            };
            document.head.appendChild(script);
        } else {
            this.renderPreview(field_id, items, chart_type);
        }
    };

    this.renderPreview = function(field_id, items, chart_type) {
        var canvas = document.getElementById('preview_' + field_id);
        
        if (!canvas) return;
        
        var ctx = canvas.getContext('2d');
        
        if (window['preview_chart_' + field_id]) {
            window['preview_chart_' + field_id].destroy();
        }
        
        var labels = items.map(function(item) { return item.label; });
        var values = items.map(function(item) { return item.value; });
        var colors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69', '#858796'];
        var chartColors = [];
        
        for (var i = 0; i < items.length; i++) {
            chartColors.push(colors[i % colors.length]);
        }
        
        var data = {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: chart_type === 'line' ? 'rgba(78, 115, 223, 0.8)' : chartColors,
                borderColor: '#ffffff',
                borderWidth: 2,
                pointBackgroundColor: chartColors,
                pointBorderColor: '#ffffff',
                pointHoverBackgroundColor: '#e74a3b',
                pointHoverBorderColor: '#ffffff'
            }]
        };
        
        var options = {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    boxWidth: 10,
                    fontSize: 10
                }
            },
            animation: {
                duration: 500
            }
        };
        
        if (chart_type === 'bar' || chart_type === 'horizontalBar' || chart_type === 'line') {
            options.scales = {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            };
            
            if (chart_type === 'horizontalBar') {
                options.scales = {
                    xAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                };
            }
        }
        
        if (chart_type === 'doughnut') {
            options.cutoutPercentage = 60;
        }
        
        window['preview_chart_' + field_id] = new Chart(ctx, {
            type: chart_type,
            data: data,
            options: options
        });
    };

    this.showNotification = function(message, type) {
        var notification = $('<div class="infographic-notification">' + message + '</div>');
        
        notification.css({
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '12px 20px',
            background: type === 'warning' ? '#f39c12' : '#e74a3b',
            color: 'white',
            borderRadius: '6px',
            boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
            zIndex: 9999,
            fontSize: '14px',
            fontWeight: 500
        });
        
        $('body').append(notification);
        
        setTimeout(function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    };

    this.escapeHtml = function(text) {
        if (!text) return '';
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    };

    return this;

}).call(icms.infographic || {}, jQuery);