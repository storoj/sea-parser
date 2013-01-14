/**
 * Created with JetBrains PhpStorm.
 * User: storoj
 * Date: 28.12.12
 * Time: 22:48
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    $('.datepicker').datepicker({language: 'ru'});

    function addGroupTab(tabID, title, phrases)
    {
        $('#settingsTabs').append('<li><a href="#settings-'+tabID+'" data-tab-id="'+tabID+'" data-toggle="tab">'+title+'</a></li>');
        $('#settings .tab-content').append('<div class="tab-pane" id="settings-'+tabID+'"><textarea name="phrases['+tabID+']">'+phrases+'</textarea></div>');
    }

    function activateFirstGroupTab()
    {
        $('#settingsTabs li:first a').tab('show');
    }

    function activateLastGroupTab()
    {
        $('#settingsTabs li:last a').tab('show');
    }

    var settingsGetQuery = new AjaxQuery({
        url: '/settings/phrases',
        callbacks: {
            success: function(status, response) {
                if (status != 'ok') {
                    alertbox(response.message, status);
                } else {
                    for (var groupDataIndex in response.result) {

                        var groupData = response.result[groupDataIndex];
                        var groupName = groupData.name;
                        var phrasesText = groupData.phrases.join("\n");

                        addGroupTab(groupDataIndex-0+1, groupName, phrasesText);
                    }
                    activateFirstGroupTab();
                }
            }
        }
    });
    settingsGetQuery.execute();

    $('#settings form').submit(function(){
        var keywordsGroupsData = [];
        $('#settingsTabs li a').each(function(i, item){
            item = $(item);
            var tabID = item.data('tab-id');
            var groupName = item.text();
            var phrases = $('textarea[name="phrases['+tabID+']"]').val().split("\n");

            keywordsGroupsData.push({name: groupName, phrases: phrases});
        });

        var settingsSaveQuery = new AjaxQuery({
            url: "/settings/phrases/save",
            callbacks: {
                success: function(status, response) {
                    alertbox(response.message, status);
                }
            }
        }, {groups: keywordsGroupsData});
        settingsSaveQuery.execute();

        return false;
    });

    $('#group_add').click(function(){
        var lastTabID = $('#settingsTabs li:last a').data('tab-id') || 0;
        var newTabID = lastTabID + 1;

        addGroupTab(newTabID, 'Группа '+newTabID, '');
        activateLastGroupTab();
        return false;
    });

    $('#settings').on('shown', 'a[data-toggle="tab"]', function (e) {
        $('#group_name').val($(e.target).text());
    });

    $('#group_name').keyup(function(e){
        $('#settings .nav-tabs .active a').text($(this).val());
    });

    $('#group_remove').click(function(){
        var activeTabID = $('#settingsTabs li.active a').data('tab-id');
        $('#settingsTabs li.active').remove();
        $('#settings-'+activeTabID).remove();
        activateFirstGroupTab();
    });
});