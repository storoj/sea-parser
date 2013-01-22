/**
 * Created with JetBrains PhpStorm.
 * User: storoj
 * Date: 14.01.13
 * Time: 8:25
 * To change this template use File | Settings | File Templates.
 */
var plotDataQuery = new AjaxQuery({
    url:"/search",
    request_type:"POST",
    callbacks:{
        success:function(status, queryResults){
            var lines = [];
            var lineLabels = [];

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

            for(var i=0; i<sortedQueryResult.length; i++) {
                var queryResult = sortedQueryResult[i];
                var documentsData = queryResult.documents;
                var linePoints = [];
                for (var j=0; j<documentsData.length; j++) {
                    var date = new Date(documentsData[j].date * 1000);
                    linePoints.push([date, j+1]);
                }
                lines.push(linePoints);
                lineLabels.push(queryResult.query + ' <b>('+queryResult.total_found+')</b>');
            }

            $('#chart1').empty();
            var plot1 = $.jqplot('chart1', lines, {
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
    }
});

bindFormToAjaxQuery('#stats form', plotDataQuery);