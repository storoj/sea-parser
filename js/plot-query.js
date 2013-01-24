/**
 * Created with JetBrains PhpStorm.
 * User: storoj
 * Date: 14.01.13
 * Time: 8:25
 * To change this template use File | Settings | File Templates.
 */

function clearAndHideOutputs()
{
    $('.output-area').hide();
    $('#plotArea').empty();
}

function displayPlot(plotData)
{
    var lines = [];
    var lineLabels = [];

    for(var i=0; i<plotData.length; i++) {
        var queryResult = plotData[i];

        var documentsData = queryResult.documents;
        var linePoints = [];
        for (var j=0; j<documentsData.length; j++) {
            var date = new Date(documentsData[j].date * 1000);
            linePoints.push([date, j+1]);
        }
        lines.push(linePoints);
        lineLabels.push(queryResult.query + ' <b>('+queryResult.total_found+')</b>');
    }

    clearAndHideOutputs();
    $('#plotContainer').show();

    $.jqplot('plotArea', lines, {
        title:'Статистика запросов',
        axes:{
            xaxis:{
                renderer:$.jqplot.DateAxisRenderer,
                tickOptions:{
                    formatString:'%b&nbsp;%#d'
                }
            },
            yaxis:{
                tickOptions:{
                    formatString:'%d'
                }
            }
        },
        highlighter: {
            show: true,
            sizeAdjust: 7.5
        },
        cursor: {
            show: false
        },
        legend: {
            show: true,
            labels: lineLabels,
            location: 'nw'
        }
    });
}

function displayTable(tableData)
{
    var tableContents = '';
    for (var i=0; i<tableData.length; i++) {
        var queryResult = tableData[i];
        tableContents += '<tr><td>'+queryResult.query+'</td><td>'+queryResult.total_found+'</td></tr>';
    }
    clearAndHideOutputs();
    $('#resultsTable').html(tableContents);
    $('#resultsTableContainer').show();
}

function displayText(plotData)
{
    var documentsHTML = '';

    for(var i=0; i<plotData.length; i++) {
        var queryResult = plotData[i];

        var documentsData = queryResult.documents;
        documentsHTML += '<h1>'+queryResult.query+' ('+queryResult.total_found+')</h1>';

        for (var j=0; j<documentsData.length; j++) {
            var documentData = documentsData[j];
            var sourceIcon = '';
            switch (parseInt(documentData.source_id)) {
                case 1:
                    sourceIcon = 'http://infranews.ru/wp-content/uploads/2012/08/favicon.ico';
                    break;
                case 2:
                    sourceIcon = 'http://seanews.ru/favicon.ico';
                    break;
                case 3:
                    sourceIcon = 'http://morvesti.ru/favicon.ico';
                    break;
                case 4:
                    sourceIcon = 'http://portnews.ru/favicon.ico';
                    break;
            }
            if (sourceIcon.length > 0) {
                sourceIcon = '<img src="'+sourceIcon+'">';
            }
            documentsHTML += '<h3><a target="_blank" href="'+documentData.source_url+'">'+sourceIcon+' '+documentData.title + '</a></h3><p>'+documentData.content+'</p>';
        }
    }
    clearAndHideOutputs();
    $('#textArea').html(documentsHTML);
    $('#textContainer').show();
}

var plotDataQuery = new AjaxQuery({
    url:"/search",
    request_type:"POST",
    callbacks:{
        success:function(status, queryResults){

            var results = queryResults.result;
            var sortedQueryResult = results.sort(function(a, b){
                if (a.total_found < b.total_found) {
                    return 1;
                }
                if (a.total_found > b.total_found) {
                    return -1;
                }
                return 0;
            });

            // TODO return verbosity flag
            var verbosity = parseInt($('input[name=verbose]').val());

            switch (verbosity) {
                case 0:
                    displayTable(sortedQueryResult);
                    break;
                case 1:
                    displayPlot(sortedQueryResult);
                    break;
                case 2:
                    displayText(sortedQueryResult);
                    break;
            }
        }
    }
});

bindFormToAjaxQuery('#stats form', plotDataQuery);

function requestPlot()
{
    $('input[name=verbose]').val('1');
    $('#resultsForm').submit();
}

function requestTable()
{
    $('input[name=verbose]').val('0');
    $('#resultsForm').submit();
}

function requestText()
{
    $('input[name=verbose]').val('2');
    $('#resultsForm').submit();
}