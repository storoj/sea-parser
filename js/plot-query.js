/**
 * Created with JetBrains PhpStorm.
 * User: storoj
 * Date: 14.01.13
 * Time: 8:25
 * To change this template use File | Settings | File Templates.
 */
var plotDataQuery = new AjaxQuery({
    url:"/ajax.php",
    request_type:"POST",
    callbacks:{
        success:function(status, queryResults){
            var lines = [];
            var lineLabels = [];
            for(var i=0; i<queryResults.length; i++) {
                var queryResult = queryResults[i];
                var documentsData = queryResult.documents;
                var linePoints = [];
                for (var j=0; j<documentsData.length; j++) {
                    var date = new Date(documentsData[j].date * 1000);
                    linePoints.push([date, j+1]);
                }
                lines.push(linePoints);
                lineLabels.push(queryResult.query + ' <b>('+queryResult.total_found+')</b>');
            }

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