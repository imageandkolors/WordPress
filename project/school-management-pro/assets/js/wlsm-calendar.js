/**
 * wlsm-calendar.js
 * Month-grid calendar for Holidays & Events.
 * Full Year View support added.
 * Depends on: jQuery, toastr (already enqueued by menu_page_assets).
 * Config injected via wp_localize_script as window.wlsmCalendar.
 */
(function ($) {
    'use strict';

    /* ── State ─────────────────────────────────────────────── */
    var today = new Date();
    var curYear  = today.getFullYear();
    var curMonth = today.getMonth() + 1; // 1-based
    var viewMode = 'month'; // 'month' or 'year'

    /* ── Config shorthand ──────────────────────────────────── */
    var cfg  = window.wlsmCalendar || {};
    var i18n = cfg.i18n || {};
    var days = i18n.days || ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    var monthNames = cfg.months || [
        'January','February','March','April','May','June',
        'July','August','September','October','November','December'
    ];

    /* ── DOM shortcuts ─────────────────────────────────────── */
    var $grid   = $('#wlsm-calendar-grid');
    var $loader = $('#wlsm-calendar-loader');
    var $title  = $('#wlsm-cal-title');

    /* ── Helpers ───────────────────────────────────────────── */
    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function toDateKey(str) {
        return str ? str.substring(0, 10) : '';
    }

    function expandRange(startStr, endStr, year, month) {
        var keys = [];
        var s = new Date(startStr + 'T00:00:00');
        var e = new Date(endStr   + 'T00:00:00');
        
        var monthStart, monthEnd;
        if (month > 0) {
            monthStart = new Date(year, month - 1, 1);
            monthEnd   = new Date(year, month, 0);
        } else {
            monthStart = new Date(year, 0, 1);
            monthEnd   = new Date(year, 11, 31);
        }

        if (s < monthStart) s = new Date(monthStart);
        if (e > monthEnd)   e = new Date(monthEnd);

        var d = new Date(s);
        while (d <= e) {
            keys.push(d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()));
            d.setDate(d.getDate() + 1);
        }
        return keys;
    }

    /* ── Render Month Grid (Elegant) ───────────────────────── */
    function renderGrid(year, month, events, holidays, exams) {
        $title.text(monthNames[month - 1] + ' ' + year);

        var firstDay = new Date(year, month - 1, 1).getDay();
        var daysInMonth = new Date(year, month, 0).getDate();
        var todayKey = today.getFullYear() + '-' + pad(today.getMonth() + 1) + '-' + pad(today.getDate());

        var eventMap = {};
        $.each(events || [], function (_, ev) {
            var key = toDateKey(ev.date_start);
            if (!eventMap[key]) eventMap[key] = [];
            eventMap[key].push(ev);
        });

        var holidayMap = {};
        $.each(holidays || [], function (_, h) {
            var keys = expandRange(h.date_start, h.date_end, year, month);
            $.each(keys, function (_, key) {
                if (!holidayMap[key]) holidayMap[key] = [];
                holidayMap[key].push(h);
            });
        });

        var examMap = {};
        $.each(exams || [], function (_, ex) {
            var key = toDateKey(ex.date_start);
            if (!examMap[key]) examMap[key] = [];
            examMap[key].push(ex);
        });

        var html = '<table class="table wlsm-calendar-table mb-0">';
        html += '<thead><tr class="text-muted">';
        $.each(days, function (_, d) {
            html += '<th class="text-center py-3" style="width:14.28%; border:none;">' + d + '</th>';
        });
        html += '</tr></thead><tbody>';

        var cellCount = 0;
        var row = '<tr>';

        for (var i = 0; i < firstDay; i++) {
            row += '<td style="background:#fcfcfd; height:120px; border: 1px solid rgba(0,0,0,0.03);"></td>';
            cellCount++;
        }

        for (var day = 1; day <= daysInMonth; day++) {
            var key = year + '-' + pad(month) + '-' + pad(day);
            var isToday = (key === todayKey);
            var hasHoliday = !!holidayMap[key];
            var hasEvent   = !!eventMap[key];
            var hasExam    = !!examMap[key];

            var cellClass = '';
            if (isToday)    cellClass += ' wlsm-today-cell';
            if (hasHoliday) cellClass += ' wlsm-cal-has-holiday';
            if (hasEvent)   cellClass += ' wlsm-cal-has-event';
            if (hasExam)    cellClass += ' wlsm-cal-has-exam';
            var cellHtml = '<td class="' + cellClass + '" style="height:120px; vertical-align:top; border: 1px solid rgba(0,0,0,0.03); padding:10px; position:relative;">';

            var dayClass = isToday ? 'text-primary' : 'text-dark';
            cellHtml += '<span class="wlsm-day-number ' + dayClass + '">' + day + '</span>';

            if (hasHoliday) {
                $.each(holidayMap[key], function (_, h) {
                    var hlabel = h.title.length > 18 ? h.title.substring(0, 18) + '…' : h.title;
                    var href   = cfg.holidaysUrl ? cfg.holidaysUrl + '&action=save&id=' + h.ID : '#';
                    cellHtml += '<a href="' + href + '" class="wlsm-pill wlsm-pill-holiday wlsm-popover-trigger" '
                        + 'data-type="holiday" data-title="' + escAttr(h.title) + '" data-desc="' + escAttr(h.description || '') + '" '
                        + 'data-date="' + formatRange(h.date_start, h.date_end) + '">'
                        + '<i class="fas fa-umbrella-beach mr-1"></i>' + escHtml(hlabel)
                        + '</a>';
                });
            }

            if (hasEvent) {
                $.each(eventMap[key], function (_, ev) {
                    var elabel = ev.title.length > 18 ? ev.title.substring(0, 18) + '…' : ev.title;
                    var href   = cfg.eventsUrl ? cfg.eventsUrl + '&action=save&id=' + ev.ID : '#';
                    cellHtml += '<a href="' + href + '" class="wlsm-pill wlsm-pill-event wlsm-popover-trigger" '
                        + 'data-type="event" data-title="' + escAttr(ev.title) + '" data-desc="' + escAttr(ev.description || '') + '" '
                        + 'data-date="' + formatDate(ev.date_start) + '">'
                        + '<i class="fas fa-calendar-check mr-1"></i>' + escHtml(elabel)
                        + '</a>';
                });
            }

            if (hasExam) {
                $.each(examMap[key], function (_, ex) {
                    var exlabel = ex.title.length > 18 ? ex.title.substring(0, 18) + '…' : ex.title;
                    var href    = cfg.examsUrl ? cfg.examsUrl + '&action=save&id=' + ex.exam_id : '#';
                    var timeText = (ex.start_time && ex.end_time) ? ex.start_time.substring(0,5) + ' - ' + ex.end_time.substring(0,5) : (ex.start_time || '');
                    cellHtml += '<a href="' + href + '" class="wlsm-pill wlsm-pill-exam wlsm-popover-trigger" '
                        + 'data-type="exam" data-title="' + escAttr(ex.title) + '" data-time="' + escAttr(timeText) + '" '
                        + 'data-room="' + escAttr(ex.room_number || '') + '" data-date="' + formatDate(ex.date_start) + '">'
                        + '<i class="fas fa-file-invoice mr-1"></i>' + escHtml(exlabel)
                        + '</a>';
                });
            }

            cellHtml += '</td>';
            row += cellHtml;
            cellCount++;

            if (cellCount % 7 === 0) {
                html += row + '</tr>';
                row = '<tr>';
            }
        }

        var remainder = cellCount % 7;
        if (remainder > 0) {
            for (var j = remainder; j < 7; j++) {
                row += '<td style="background:#fcfcfd; height:120px; border: 1px solid rgba(0,0,0,0.03);"></td>';
            }
            html += row + '</tr>';
        }

        html += '</tbody></table>';

        var totalItems = (events ? events.length : 0) + (holidays ? holidays.length : 0) + (exams ? exams.length : 0);
        if (totalItems === 0) {
            html += '<div class="text-center text-muted py-5">'
                  + '<i class="fas fa-calendar-day fa-3x mb-3 opacity-25"></i>'
                  + '<p>' + (i18n.noData || 'No events or holidays this month.') + '</p>'
                  + '</div>';
        }

        $grid.html(html);
    }

    /* ── Render Year Grid (Elegant List) ───────────────────── */
    function renderYearGrid(year, events, holidays, exams) {
        $title.text(year);

        var monthlyData = {};
        for (var i = 1; i <= 12; i++) {
            monthlyData[i] = { events: [], holidays: [], exams: [] };
        }

        $.each(events || [], function (_, ev) {
            var m = new Date(ev.date_start + 'T00:00:00').getMonth() + 1;
            monthlyData[m].events.push(ev);
        });

        $.each(holidays || [], function (_, h) {
            for (var m = 1; m <= 12; m++) {
                var keys = expandRange(h.date_start, h.date_end, year, m);
                if (keys.length > 0) {
                    monthlyData[m].holidays.push(h);
                }
            }
        });

        $.each(exams || [], function (_, ex) {
            var m = new Date(ex.date_start + 'T00:00:00').getMonth() + 1;
            monthlyData[m].exams.push(ex);
        });

        var html = '<div class="row px-2 py-4">';
        var monthsFound = 0;

        for (var m = 1; m <= 12; m++) {
            var items = [];
            $.each(monthlyData[m].holidays, function(_, h) {
                items.push({ date: h.date_start, title: h.title, type: 'holiday', id: h.ID, end: h.date_end, desc: h.description });
            });
            $.each(monthlyData[m].events, function(_, ev) {
                items.push({ date: ev.date_start, title: ev.title, type: 'event', id: ev.ID, desc: ev.description });
            });
            $.each(monthlyData[m].exams, function(_, ex) {
                items.push({ date: ex.date_start, title: ex.title, type: 'exam', id: ex.exam_id, start: ex.start_time, end: ex.end_time, room: ex.room_number });
            });

            // Sort items by date
            items.sort(function(a, b) {
                return a.date.localeCompare(b.date);
            });

            if (items.length === 0) continue;
            monthsFound++;

            html += '<div class="col-md-4 mb-5">';
            html += '<h6 class="wlsm-year-grid-month-title wlsm-view-month text-left border-bottom pb-2 mb-3" data-month="' + m + '" style="cursor:pointer; border-color: #eef0f2 !important;">' 
                  + monthNames[m-1] 
                  + ' <span class="text-muted font-weight-normal ml-1" style="font-size: 12px; opacity: 0.6;">(' + items.length + ')</span>'
                  + '</h6>';
            
            html += '<div class="pl-1">';
            $.each(items, function (_, item) {
                var href, icon, pillClass, dateText, dataAttrs;
                if (item.type === 'holiday') {
                    href = cfg.holidaysUrl ? cfg.holidaysUrl + '&action=save&id=' + item.id : '#';
                    icon = 'fa-umbrella-beach';
                    pillClass = 'wlsm-pill-holiday';
                    dateText = formatRange(item.date, item.end);
                    dataAttrs = 'data-type="holiday" data-title="' + escAttr(item.title) + '" data-desc="' + escAttr(item.desc || '') + '" data-date="' + dateText + '"';
                } else if (item.type === 'exam') {
                    href = cfg.examsUrl ? cfg.examsUrl + '&action=save&id=' + item.id : '#';
                    icon = 'fa-file-invoice';
                    pillClass = 'wlsm-pill-exam';
                    dateText = formatDate(item.date);
                    var timeText = (item.start && item.end) ? item.start.substring(0,5) + ' - ' + item.end.substring(0,5) : (item.start || '');
                    dataAttrs = 'data-type="exam" data-title="' + escAttr(item.title) + '" data-time="' + escAttr(timeText) + '" data-room="' + escAttr(item.room || '') + '" data-date="' + dateText + '"';
                } else {
                    href = cfg.eventsUrl ? cfg.eventsUrl + '&action=save&id=' + item.id : '#';
                    icon = 'fa-calendar-check';
                    pillClass = 'wlsm-pill-event';
                    dateText = formatDate(item.date);
                    dataAttrs = 'data-type="event" data-title="' + escAttr(item.title) + '" data-desc="' + escAttr(item.desc || '') + '" data-date="' + dateText + '"';
                }

                html += '<div class="wlsm-year-list-item">'
                      + '<a href="' + href + '" class="text-dark font-weight-bold d-block mb-1 wlsm-popover-trigger" style="text-decoration:none; font-size: 14px;" ' + dataAttrs + '>' + escHtml(item.title) + '</a>'
                      + '<span class="' + pillClass + ' px-2 py-0" style="font-size: 10px; border-radius: 2px; display: inline-block;">'
                      + '<i class="fas ' + icon + ' mr-1"></i>' + dateText
                      + '</span></div>';
            });
            html += '</div></div>';
        }
        if (monthsFound === 0) {
            html = '<div class="col-12 text-center py-5 text-muted">'
                 + '<i class="fas fa-calendar-times fa-4x mb-4 opacity-10"></i>'
                 + '<h5 class="font-weight-light">' + (i18n.noDataYear || 'No events or holidays found for this year.') + '</h5>'
                 + '</div>';
        } else {
            html += '</div>';
        }

        $grid.html(html);
    }

    function renderMiniMonth(year, month, holidayMap, eventMap) {
        var firstDay = new Date(year, month - 1, 1).getDay();
        var daysInMonth = new Date(year, month, 0).getDate();
        var todayKey = today.getFullYear() + '-' + pad(today.getMonth() + 1) + '-' + pad(today.getDate());

        var html = '<table class="table table-sm table-borderless mb-0" style="line-height: 1;">';
        html += '<thead><tr class="text-muted" style="font-size: 9px; opacity: 0.6;">';
        $.each(['S','M','T','W','T','F','S'], function (_, d) {
            html += '<th class="text-center p-0 font-weight-bold" style="width:14.28%; border:none;">' + d + '</th>';
        });
        html += '</tr></thead><tbody><tr>';

        var cellCount = 0;
        for (var i = 0; i < firstDay; i++) {
            html += '<td></td>';
            cellCount++;
        }

        for (var day = 1; day <= daysInMonth; day++) {
            var key = year + '-' + pad(month) + '-' + pad(day);
            var isToday = (key === todayKey);
            var hasHoliday = !!holidayMap[key];
            var hasEvent   = !!eventMap[key];

            var styleClass = 'wlsm-mini-day';
            var inlineStyle = '';
            
            if (isToday) {
                inlineStyle = 'background:#007bff; color:#fff; font-weight:700;';
            } else if (hasHoliday) {
                inlineStyle = 'background:#fff9db; color:#856404; font-weight:700;';
            } else if (hasEvent) {
                inlineStyle = 'background:#e7f3ff; color:#007bff; font-weight:700;';
            } else {
                inlineStyle = 'color:#718096;';
            }

            html += '<td class="p-0 text-center wlsm-view-month" data-month="' + month + '" style="padding: 2px 0 !important;">';
            html += '<span class="' + styleClass + '" style="' + inlineStyle + '">' + day + '</span>';
            html += '</td>';
            cellCount++;

            if (cellCount % 7 === 0 && day < daysInMonth) {
                html += '</tr><tr>';
            }
        }
        html += '</tr></tbody></table>';
        return html;
    }

    function formatRange(start, end) {
        var s = new Date(start + 'T00:00:00');
        var e = new Date(end + 'T00:00:00');
        if (start === end) return formatDate(start);
        return s.getDate() + ' ' + monthNames[s.getMonth()].substring(0,3) + ' - ' + e.getDate() + ' ' + monthNames[e.getMonth()].substring(0,3);
    }

    function formatDate(date) {
        var d = new Date(date + 'T00:00:00');
        return d.getDate() + ' ' + monthNames[d.getMonth()];
    }

    /* ── AJAX fetch ────────────────────────────────────────── */
    function fetchAndRender(year, month) {
        if (!$grid.length) return; // Don't fetch if there's no grid to render into.
        $loader.show();
        $grid.html('');

        $.post(cfg.ajaxUrl, {
            action : cfg.action || 'wlsm-fetch-calendar-events',
            nonce  : cfg.nonce,
            year   : year,
            month  : (viewMode === 'year' ? 0 : month)
        })
        .done(function (res) {
            $loader.hide();
            if (res && res.success && res.data) {
                if (viewMode === 'year') {
                    renderYearGrid(year, res.data.events, res.data.holidays, res.data.exams);
                } else {
                    renderGrid(year, month, res.data.events, res.data.holidays, res.data.exams);
                }
            } else {
                toastr.error(i18n.error || 'Failed to load calendar data.');
            }
        })
        .fail(function () {
            $loader.hide();
            toastr.error(i18n.error || 'Failed to load calendar data.');
        });
    }

    /* ── Popover Implementation ────────────────────────────── */
    var $activePopover = null;

    function showPopover($trigger) {
        destroyPopover();

        var type  = $trigger.data('type');
        var title = $trigger.data('title');
        var date  = $trigger.data('date');
        var desc  = $trigger.data('desc') || '';
        var time  = $trigger.data('time') || '';
        var room  = $trigger.data('room') || '';

        var typeLabel = type.charAt(0).toUpperCase() + type.slice(1);
        var typeClass = 'wlsm-pop-badge-' + type;

        var html = '<div class="wlsm-calendar-popover" id="wlsm-active-popover">'
                 + '<div class="wlsm-pop-header ' + type + '">'
                 + '<span class="wlsm-pop-type ' + typeClass + '">' + typeLabel + '</span>'
                 + '<h6 class="wlsm-pop-title">' + escHtml(title) + '</h6>'
                 + '</div>'
                 + '<div class="wlsm-pop-body">'
                 + '<div class="wlsm-pop-item"><i class="far fa-calendar-alt"></i><span>' + escHtml(date) + '</span></div>';

        if (time) {
            html += '<div class="wlsm-pop-item"><i class="far fa-clock"></i><span>' + escHtml(time) + '</span></div>';
        }
        if (room) {
            html += '<div class="wlsm-pop-item"><i class="fas fa-door-open"></i><span>' + escHtml(room) + '</span></div>';
        }
        if (desc) {
            html += '<div class="wlsm-pop-divider"></div>'
                 + '<div class="wlsm-pop-desc">' + escHtml(desc) + '</div>';
        }

        html += '</div></div>';

        $activePopover = $(html).appendTo('body');

        var offset = $trigger.offset();
        var popW = $activePopover.outerWidth();
        var popH = $activePopover.outerHeight();
        var winW = $(window).width();

        var left = offset.left + ($trigger.outerWidth() / 2) - (popW / 2);
        var top  = offset.top - popH - 10;

        // Contain in viewport
        if (left < 10) left = 10;
        if (left + popW > winW - 10) left = winW - popW - 10;
        if (top < $(window).scrollTop() + 10) {
            top = offset.top + $trigger.outerHeight() + 10;
            $activePopover.addClass('bottom');
        }

        $activePopover.css({ left: left, top: top }).addClass('show');
    }

    function destroyPopover() {
        if ($activePopover) {
            $activePopover.remove();
            $activePopover = null;
        }
    }

    /* ── Escape helpers ────────────────────────────────────── */
    function escHtml(str) { return $('<div>').text(str).html(); }
    function escAttr(str) { return $('<div>').text(str).html().replace(/"/g, '&quot;'); }

    /* ── Navigation ────────────────────────────────────────── */
    function navigate(delta) {
        if (viewMode === 'year') {
            curYear += delta;
        } else {
            curMonth += delta;
            if (curMonth > 12) { curMonth = 1;  curYear++; }
            if (curMonth < 1)  { curMonth = 12; curYear--; }
        }
        fetchAndRender(curYear, curMonth);
    }

    /* ── Boot ──────────────────────────────────────────────── */
    $(document).ready(function () {
        $(document).on('click', '#wlsm-cal-prev',  function () { navigate(-1); });
        $(document).on('click', '#wlsm-cal-next',  function () { navigate(1);  });
        $(document).on('click', '#wlsm-cal-today', function () {
            viewMode = 'month';
            curYear  = today.getFullYear();
            curMonth = today.getMonth() + 1;
            $(this).addClass('active');
            $('#wlsm-cal-year').removeClass('active');
            fetchAndRender(curYear, curMonth);
        });

        $(document).on('click', '#wlsm-cal-year', function () {
            if (viewMode === 'year') {
                viewMode = 'month';
                $(this).removeClass('active');
                $('#wlsm-cal-today').addClass('active');
            } else {
                viewMode = 'year';
                $(this).addClass('active');
                $('#wlsm-cal-today').removeClass('active');
            }
            fetchAndRender(curYear, curMonth);
        });

        $(document).on('click', '.wlsm-view-month', function () {
            var m = $(this).data('month');
            if (m) {
                curMonth = m;
                viewMode = 'month';
                $('#wlsm-cal-year').removeClass('active');
                $('#wlsm-cal-today').addClass('active');
                fetchAndRender(curYear, curMonth);
            }
        });

        // Hover Popover events
        $(document).on('mouseenter', '.wlsm-popover-trigger', function () {
            showPopover($(this));
        });
        $(document).on('mouseleave', '.wlsm-popover-trigger', function () {
            destroyPopover();
        });

        fetchAndRender(curYear, curMonth);
    });

}(jQuery));
