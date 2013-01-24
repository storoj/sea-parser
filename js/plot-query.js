/**
 * Created with JetBrains PhpStorm.
 * User: storoj
 * Date: 14.01.13
 * Time: 8:25
 * To change this template use File | Settings | File Templates.
 */

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

    $('#resultsTableContainer').hide();
    $('#plotContainer').show();
    $('#plotArea').empty();

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
    $('#resultsTableContainer').show();
    $('#plotContainer').hide();
    $('#plotArea').empty();

    $('#resultsTable').html(tableContents);
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
            var isPlotResults = $('input[name=verbose]').val() == '1';

            if (isPlotResults) {
                displayPlot(sortedQueryResult);
            } else {
                displayTable(sortedQueryResult);
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