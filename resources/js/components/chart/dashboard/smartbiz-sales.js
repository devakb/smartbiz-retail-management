import { mountChart, readConfig } from '../helpers';

export default (el) => {
    const { categories, data, seriesName } = readConfig(el);

    mountChart(el, (dark) => ({
        series: [{ name: seriesName || 'Sales', data: data || [] }],
        colors: ['#465FFF'],
        chart: {
            fontFamily: 'Outfit, sans-serif',
            type: 'bar',
            height: 260,
            toolbar: { show: false },
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '42%',
                borderRadius: 6,
                borderRadiusApplication: 'end',
            },
        },
        dataLabels: { enabled: false },
        stroke: { show: false },
        xaxis: {
            categories: categories || [],
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: dark ? '#94A3B8' : '#364153', fontFamily: 'Outfit, sans-serif' } },
        },
        legend: { show: false },
        yaxis: {
            labels: {
                style: { colors: dark ? '#94A3B8' : '#364153', fontFamily: 'Outfit, sans-serif' },
                formatter: (val) => Number(val).toLocaleString(),
            },
        },
        grid: {
            borderColor: dark ? '#1E2640' : '#F3F4F6',
            strokeDashArray: 0,
            yaxis: { lines: { show: true } },
            xaxis: { lines: { show: false } },
        },
        tooltip: {
            y: { formatter: (val) => Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
        },
    }));
};
