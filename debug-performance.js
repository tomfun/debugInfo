if (typeof jQuery != 'function') {
    alert('Debug info require jQuery, did your forgot leading slash');
}
jQuery(function ($) {
    $('head').append('<link rel="stylesheet" type="text/css" href="/debug-performance.css" media="all" />');
    var tooltip = $('<div class="debug-info-tooltip" style="position: absolute;"></div>').appendTo($('body'));
    var infos = $('.debug-info-container');
/*    infos.click(function () {
        debugger;
        console.log($(this).data());
    });*/
    var prev = null, original = null;
    infos.hover(
        function () {
            var it = $(this),
                data = it.data(),
                out = '';
            if (it == tooltip) {
                return;
            }
            for (var i in data) {
                out += i + ': ' + data[i] + '<br/>';
            }
            tooltip.html(out);
            tooltip.css(it.offset());
            tooltip.css({
                'z-index': 999,
                'background-color': '#eee',
                opacity: 0.6,
                'font-weight': 'bold'
            });
            original = {
                border: it.css('border'),
                opacity: it.css('opacity')
            };
            prev = it;
            it.css({
                'border': 'dashed 1px red',
                'opacity': 0.8
            });
            tooltip.show();
            console.log(data);
        },
        function () {
            if (prev) prev.css(original);
            tooltip.hide();
        }
    );
    var decorateDiff = function(diff) {
        var diffStyles = '';
        if (diff < 0.001) {
            diffStyles = 'opacity: 0.5';
        } else {
            if (diff < 0.01) {
                diffStyles = 'opacity: 1';
            } else {
                if (diff < 0.05) {
                    diffStyles = 'color: #990000;';
                } else {
                    if (diff < 0.1) {
                        diffStyles = 'color: #990000; font-weight: bolder;';
                    } else {
                        if (diff > 5) {
                            diffStyles = 'color: #ff0000; font-weight: bolder; text-decoration: underline;';
                        } else {
                            diffStyles = 'color: #ff0000; font-weight: bolder;';
                        }
                    }
                }
            }
        }
        return diffStyles;
    };
    var likeOverallSections = ['overall', 'load from cache', 'abstract_to_html_after+', 'afterToHtml'];
    var overallInfo = $('<div class="debug-info-overall" style="position: absolute;"></div>').appendTo($('body'));
    var html,
        firstTime = null;
    for (var j in debugInfoPerformance) {
        if (!debugInfoPerformance.hasOwnProperty(j)) { continue; }
        if (likeOverallSections.indexOf(j) < 0)  { continue; }
        html = '';
        var section = debugInfoPerformance[j];
        firstTime = section && section.hasOwnProperty(1)
            ? section[1] : null;
        for (var i in section) {
            var time = section[i];
            if (i == 'finish') {
                var tr = '<tr>'
                    + '<td title="finish" colspan="4">' + time + '</td>'
                    + '<td title="diff overall">' + (time - firstTime) + '</td>'
                    + '</tr>';
                html += tr;
                html = '<table class="collapsible-table">'
                + '<thead><tr><td colspan="3">' + j
                + '</td></tr></thead><tbody>'
                + html
                + '</tbody></table>';
                overallInfo.append(html);
                break;
            }
            if (!debugInfoIds.hasOwnProperty(i)) {
                continue
            }
            var diff = (section['fin_' + i] - time);

            tr = '<tr data-block-id="' + i + '">'
            + '<td title="name">' + debugInfoIds[i].name + '</td>'
            + '<td title="template">' + debugInfoIds[i].template + '</td>'
            + '<td title="class">' + debugInfoIds[i].class + '</td>'
            + '<td title="time">' + time + '</td>'
            + '<td title="difference seconds" style="text-align: right;'
            + decorateDiff(diff) + '">' + diff.toFixed(4) + '</td>'
            + '</tr>';
            html += tr;
        }
    }
    overallInfo.find('tr').click(function(){ console.log(debugInfoIds[$(this).data('block-id')]);});

    for (var uniq in debugInfoPerformance) {
        if (!debugInfoPerformance.hasOwnProperty(uniq)) { continue; }
        if (likeOverallSections.indexOf(uniq) >= 0)  { continue; }
        var labels = debugInfoPerformance[uniq];
        var lastTime = labels.hasOwnProperty('all')
                    ?  labels['all'] : null;
        html = '';
        for (i in labels) {
            if (i == 'all') { continue; }
            time = labels[i];
            if (i == 'finish') {
                tr = '<tr>'
                    + '<td title="finish">' + i + '</td>'
                    + '<td title="time">' + time + '</td>'
                    + '<td title="difference seconds overall" style="text-align: right; padding-left: 0.7em;'
                        + decorateDiff(diff)
                        + '">' + (time - labels['all']).toFixed(4) + '</td>'
                    + '</tr>';
                html += tr;
                html = '<table class="debug-info-dump-table">'
                    + '<thead><tr><td colspan="3"><strong>'
                    + uniq + '</strong></td></tr></thead>'
                    + html + '</table>';
                overallInfo.append(html);
                break;
            }
            if (lastTime === null) {lastTime = time;}
            diff = (time - lastTime);
            lastTime = time;
            tr = '<tr data-block-id="' + i + '">'
            + '<td title="name">' + i + '</td>'
            + '<td title="time">' + time + '</td>'
            + '<td title="difference seconds" style="text-align: right;  padding-left: 0.7em;' + decorateDiff(diff)
                + '">' + diff.toFixed(4) + '</td>'
            + '</tr>';
            html += tr;
        }
    }
    // log table
    (function() {
        html = '';
        for(i in debugInfoOutLog) {
            if (!debugInfoOutLog.hasOwnProperty(i))  { continue; }
            var out = debugInfoOutLog[i];
            out.forEach(function(a){
                var data = !!a.blockId ? 'data-block-id="' + a.blockId + '"' : '',
                    cssClass = (!!data ? ' ' : '') + 'class="' + a.class + '"';
                html += '<tr ' + data + cssClass + '>'
                + '<td title="name">' + i + '</td>'
                + '<td title="message">' + a.message + '</td>'
                + '</tr>';
            });
        }
        if (html) {
            html = '<table class="debug-info-log-table">'
            + '<thead><tr><td colspan="3">Log</td></tr></thead><tbody>'
            + html +'</tbody></table>';
            overallInfo.append(html);
        }
    })();
    // direct console output
    (function() {
        html = '';
        for(i in debugDirectOutLog) {
            if (!debugDirectOutLog.hasOwnProperty(i))  { continue; }
            var out = debugDirectOutLog[i],
                obj = JSON.parse(out.object);
            if (!!out.varName) {
                console.log(out.varName);
                window[out.varName] = obj;
            }
            console.log(out);
            console.log(obj);
        }
    })();
    // profiler table
    (function() {
        html = '';
        var profilerArr = [],
            j = 0;
        for(i in debugInfoProfiler) {
            if (!debugInfoProfiler.hasOwnProperty(i)) {
                continue;
            }
            var val = debugInfoProfiler[i];
            val.name = i;
            val.index = j++;
            val.avg = val.sum / val.count;
            profilerArr.push(debugInfoProfiler[i])
        }
        var sortAttribute = 'index',
            sortOrder = 1,
            sortFunc = function(a,b){
                var x = a[sortAttribute],
                    y = b[sortAttribute];
                if (x == y) {
                    return 0;
                } else {
                    if (x > y) {
                        return -sortOrder;
                    } else {
                        return sortOrder;
                    }
                }
            };
        var tmp = profilerArr.sort(sortFunc);
        var buildTBody = function(tmp) {
            var html = '';
            tmp.forEach(function(val, i, arr){
                html += '<tr>'
                + '<td title="name">' + val.name + '</td>'
                + '<td title="count">' + val.count + '</td>'
                + '<td title="emalloc">' + val.emalloc + '</td>'
                + '<td title="emalloc_start">' + val.emalloc_start + '</td>'
                + '<td title="realmem">' + val.realmem + '</td>'
                + '<td title="realmem_start">' + val.realmem_start + '</td>'
                + '<td title="start">' + val.start + '</td>'
                + '<td title="difference seconds (sum)" title="difference seconds overall" style="text-align: right; padding-left: 0.7em;'
                + decorateDiff(val.sum)
                + '">' + val.sum.toFixed(4) + '</td>'
                + '<td title="difference seconds" title="difference seconds overall" style="text-align: right; padding-left: 0.7em;'
                + decorateDiff(val.avg)
                + '">' + (val.avg).toFixed(4) + '</td>'
                + '</tr>';
            });
            return html;
        };
        html = buildTBody(tmp);
        if (html) {
            html = '<table class="debug-info-profiler-table collapsible-table">'
            + '<thead>' +
            '<tr class="sortable-header" title="Profiler">' +
            '<td data-sort="name">name</td>' +
            '<td data-sort="count">count</td>' +
            '<td data-sort="emalloc">emalloc</td>' +
            '<td data-sort="emalloc_start">emalloc_start</td>' +
            '<td data-sort="realmem">realmem</td>' +
            '<td data-sort="realmem_start">realmem_start</td>' +
            '<td data-sort="started">started</td>' +
            '<td data-sort="sum" style="text-align: right; padding-left: 0.7em;">sum</td>' +
            '<td data-sort="avg" style="text-align: right; padding-left: 0.7em;">avg</td>' +
            '</tr>' +
            '</thead><tbody>'
            + html +'</tbody></table>';
            overallInfo.append(html);
            var table = $('.debug-info-profiler-table'),
                width = table.find('tbody').width();
            table.width(width);
            table.find('.sortable-header td').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                var it = $(this);
                sortAttribute = it.data('sort');
                it.addClass('active-sort');
                sortOrder = it.hasClass('sort-asc') ? 1 : -1;
                it.toggleClass('sort-asc');
                tmp = profilerArr.sort(sortFunc);
                tmp = buildTBody(tmp);
                it.closest('table').find('tbody').html(tmp);
            })
        }
    })();
    $('.collapsible-table').find('thead').click(function(){
        var it = $(this),
            table = it.closest('table.collapsible-table'),
            tbody = table.find('tbody');
        table.width(tbody.width());
        tbody.slideToggle(2000, function(){
            if(typeof(Storage) !== "undefined") {
                localStorage.setItem('debug_info_table' + it.text(), tbody.css('display'));
            }
        });

    }).each(function(){
        if(typeof(Storage) !== "undefined") {
            var it = $(this),
                table = it.closest('table.collapsible-table'),
                tbody = table.find('tbody'),
                display = localStorage.getItem('debug_info_table' + it.text());
            tbody.css('display', display);
        }
    });
    // popup block info
    (function() {
        var tooltipBlock = $('<div class="debug-info-tooltip-block"></div>');
        overallInfo.append(tooltipBlock);
        var show = -1;
        overallInfo.find('tr[data-block-id]').on('mouseenter', function(){
            var it = $(this),
                blockId = it.data('block-id'),
                block = debugInfoIds[blockId],
                out = '';
            if ((blockId == undefined) || (show == blockId)) {
                return;
            }
            show = blockId;
            for (var i in block) {
                if (!block.hasOwnProperty(i))  { continue; }
                if (i != 'children' && i != 'parentId') {
                    out += i + ': ' + block[i] + '<br/>';
                } else {
                    out += '<div class="' + i + '">';
                    if (i == 'parentId') {
                        for (var j in debugInfoIds[block[i]]) {
                            if (!debugInfoIds[block[i]].hasOwnProperty(j)) {
                                continue;
                            }
                            var ch = debugInfoIds[block[i]][j];
                            out += j + ': ' + ch + '<br/>';
                        }
                    } else {
                        for (var j in block[i]) {
                            if (!block[i].hasOwnProperty(j)) {
                                continue;
                            }
                            var ch = block[i][j];
                            out += j + ' [' + ch + ']<br/>';
                        }
                    }
                    out += '</div>';
               }
            }
            tooltipBlock.css(it.position());
            tooltipBlock.css({'z-index': 999});
            tooltipBlock.html(out).slideDown(500);
        }).on('mouseleave', function(){
            var it = $(this),
                blockId = it.data('block-id');
            if (blockId != show)
                tooltipBlock.slideUp(500);
        });
    })();
    $('#debug-info-content-list-container table tr').each(function () {
        var it = $(this),
            td = it.find('td:nth-child(4)'),
            num = td.text();
        num = parseFloat(num);
        if (!isNaN(num)) {
            td.attr('style', decorateDiff(num));
        }
    });
//--- no script

});
