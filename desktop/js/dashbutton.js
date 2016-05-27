
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

$("#table_cmd").delegate(".listEquipementInfo", 'click', function() {
    var el = $(this);
    jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function(result) {
        var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=calcul]');
        calcul.atCaret('insert', result.human);
    });
});

$("#table_cmd").delegate(".listEquipementAction", 'click', function() {
    var el = $(this);
    jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function(result) {
        var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=' + el.attr('data-input') + ']');
        calcul.value(result.human);
    });
});

$('.changeIncludeState').on('click', function () {
    var el = $(this);
    jeedom.config.save({
        plugin : 'dashbutton',
        configuration: {include_mode: el.attr('data-state')},
        error: function (error) {
          $('#div_alert').showAlert({message: error.message, level: 'danger'});
      },
      success: function () {
        if (el.attr('data-state') == 1) {
            $.hideAlert();
            $('.changeIncludeState:not(.card)').removeClass('btn-default').addClass('btn-success');
            $('.changeIncludeState').attr('data-state', 0);
            $('.changeIncludeState.card').css('background-color','#8000FF');
            $('.changeIncludeState.card span center').text('{{Arrêter l\'inclusion}}');
            $('.changeIncludeState:not(.card)').html('<i class="fa fa-sign-in fa-rotate-90"></i> {{Arreter inclusion}}');
            $('#div_inclusionAlert').showAlert({message: '{{Vous etes en mode inclusion. Recliquez sur le bouton d\'inclusion pour sortir de ce mode}}', level: 'warning'});
        } else {
            $.hideAlert();
            $('.changeIncludeState:not(.card)').addClass('btn-default').removeClass('btn-success btn-danger');
            $('.changeIncludeState').attr('data-state', 1);
            $('.changeIncludeState:not(.card)').html('<i class="fa fa-sign-in fa-rotate-90"></i> {{Mode inclusion}}');
            $('.changeIncludeState.card span center').text('{{Mode inclusion}}');
            $('.changeIncludeState.card').css('background-color','#ffffff');
            $('#div_inclusionAlert').hideAlert();
        }
    }
});
});

$('body').on('dashbutton::includeDevice', function (_event,_options) {
    if (modifyWithoutSave) {
        $('#div_inclusionAlert').showAlert({message: '{{Un périphérique vient d\'être inclu/exclu. Veuillez réactualiser la page}}', level: 'warning'});
    } else {
        if (_options == '') {
            window.location.reload();
        } else {
            window.location.href = 'index.php?v=d&p=dashbutton&m=dashbutton&id=' + _options;
        }
    }
});

$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }

    if (init(_cmd.type) == 'info') {
        var disabled = (init(_cmd.configuration.virtualAction) == '1') ? 'disabled' : '';
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
        tr += '<td>';
			tr += '<span class="cmdAttr" data-l1key="id"></span>';
        tr += '</td>';
        tr += '<td>';
			tr += '<span class="cmdAttr" data-l1key="name"></span></td>';
        tr += '<td>';
        tr += '<span class="cmdAttr" data-l1key="configuration" data-l2key="value"></span>';
        tr += '</td>';
        tr += '<td>';
        tr += '<span><input type="checkbox" data-size="mini" data-label-text="{{Afficher}}" class="cmdAttr bootstrapSwitch" data-l1key="isVisible" checked/></span> ';
        tr += '</td>';
        tr += '<td>';
        if (is_numeric(_cmd.id)) {
            tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        }
        tr += '</td>';
        tr += '</tr>';
        $('#table_cmd tbody').append(tr);
        $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
        /*if (isset(_cmd.type)) {
            $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
        }
        jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));*/
    }

}
