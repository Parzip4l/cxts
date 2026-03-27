import ApexCharts from "apexcharts";

window.ApexCharts = ApexCharts;

function renderChart(selector, options) {
    const element = document.querySelector(selector);
    if (!element) {
        return;
    }

    const chart = new ApexCharts(element, options);
    chart.render();
}

function buildSeriesFromSummary(summary) {
    return [
        summary?.on_time ?? 0,
        summary?.breached ?? 0,
        summary?.pending ?? 0,
    ];
}

function renderOperationsDashboardCharts(payload) {
    const overview = payload?.overview ?? {};
    const sla = overview?.sla ?? {};
    const inspection = overview?.inspection_summary ?? {};
    const trend = overview?.daily_trend ?? [];
    const topEngineers = overview?.top_engineers ?? [];

    renderChart("#ops-daily-trend-chart", {
        chart: { type: "line", height: 320, toolbar: { show: false } },
        stroke: { curve: "smooth", width: 3 },
        series: [
            { name: "Created", data: trend.map((item) => item.created ?? 0) },
            { name: "Completed", data: trend.map((item) => item.completed ?? 0) },
        ],
        xaxis: { categories: trend.map((item) => item.date ?? "") },
        colors: ["#0d6efd", "#17a34a"],
        legend: { position: "top" },
    });

    renderChart("#ops-sla-response-chart", {
        chart: { type: "donut", height: 300 },
        labels: ["On Time", "Breached", "Pending"],
        series: buildSeriesFromSummary(sla.response),
        colors: ["#16a34a", "#dc3545", "#f59e0b"],
        legend: { position: "bottom" },
    });

    renderChart("#ops-sla-resolution-chart", {
        chart: { type: "donut", height: 300 },
        labels: ["On Time", "Breached", "Pending"],
        series: buildSeriesFromSummary(sla.resolution),
        colors: ["#0ea5e9", "#ef4444", "#f59e0b"],
        legend: { position: "bottom" },
    });

    renderChart("#ops-inspection-result-chart", {
        chart: { type: "radialBar", height: 300 },
        series: [inspection.normal_rate ?? 0],
        labels: ["Normal Result Rate"],
        colors: ["#22c55e"],
        plotOptions: {
            radialBar: {
                hollow: { size: "60%" },
                dataLabels: {
                    value: {
                        formatter: function (val) {
                            return `${Number(val).toFixed(2)}%`;
                        },
                    },
                },
            },
        },
    });

    renderChart("#ops-top-engineer-score-chart", {
        chart: { type: "bar", height: 320, toolbar: { show: false } },
        series: [
            {
                name: "Effectiveness Score",
                data: topEngineers.map((item) => Number(item.effectiveness_score ?? 0)),
            },
            {
                name: "Completion Rate",
                data: topEngineers.map((item) => Number(item.completion_rate ?? 0)),
            },
        ],
        xaxis: { categories: topEngineers.map((item) => item.engineer_name ?? "-") },
        colors: ["#4f46e5", "#0ea5e9"],
        legend: { position: "top" },
    });
}

function renderSlaPerformanceCharts(payload) {
    const data = payload?.slaPerformance ?? {};
    const summary = data?.summary ?? {};
    const breaches = data?.breach_tickets ?? [];
    const trend = data?.daily_breach_trend ?? [];

    renderChart("#sla-breach-trend-chart", {
        chart: { type: "area", height: 320, toolbar: { show: false } },
        series: [{ name: "Breached Tickets", data: trend.map((item) => item.breached ?? 0) }],
        xaxis: { categories: trend.map((item) => item.date ?? "") },
        colors: ["#ef4444"],
        dataLabels: { enabled: false },
        stroke: { curve: "smooth", width: 3 },
    });

    renderChart("#sla-response-breakdown-chart", {
        chart: { type: "pie", height: 300 },
        labels: ["On Time", "Breached", "Pending"],
        series: buildSeriesFromSummary(summary.response),
        colors: ["#16a34a", "#dc3545", "#f59e0b"],
        legend: { position: "bottom" },
    });

    renderChart("#sla-resolution-breakdown-chart", {
        chart: { type: "pie", height: 300 },
        labels: ["On Time", "Breached", "Pending"],
        series: buildSeriesFromSummary(summary.resolution),
        colors: ["#0ea5e9", "#ef4444", "#f59e0b"],
        legend: { position: "bottom" },
    });

    renderChart("#sla-late-minutes-chart", {
        chart: { type: "bar", height: 320, toolbar: { show: false } },
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 3,
            },
        },
        series: [
            {
                name: "Late Minutes",
                data: breaches.map((item) =>
                    Number(item.response_late_minutes ?? 0) + Number(item.resolution_late_minutes ?? 0),
                ),
            },
        ],
        xaxis: { categories: breaches.map((item) => item.ticket_number ?? "-") },
        colors: ["#dc2626"],
        dataLabels: { enabled: false },
    });
}

function renderEngineerEffectivenessCharts(payload) {
    const data = payload?.engineerEffectiveness ?? {};
    const engineers = data?.engineers ?? [];

    renderChart("#eng-effectiveness-score-chart", {
        chart: { type: "bar", height: 340, toolbar: { show: false } },
        series: [{ name: "Effectiveness Score", data: engineers.map((item) => Number(item.effectiveness_score ?? 0)) }],
        xaxis: { categories: engineers.map((item) => item.engineer_name ?? "-") },
        colors: ["#4f46e5"],
        dataLabels: { enabled: false },
    });

    renderChart("#eng-ticket-outcome-chart", {
        chart: { type: "bar", height: 340, stacked: true, toolbar: { show: false } },
        series: [
            { name: "Completed", data: engineers.map((item) => item.completed_tickets ?? 0) },
            { name: "Open", data: engineers.map((item) => item.open_tickets ?? 0) },
        ],
        xaxis: { categories: engineers.map((item) => item.engineer_name ?? "-") },
        colors: ["#16a34a", "#f97316"],
        legend: { position: "top" },
    });

    renderChart("#eng-sla-compliance-chart", {
        chart: { type: "radar", height: 340, toolbar: { show: false } },
        series: [
            { name: "Response SLA %", data: engineers.map((item) => Number(item.response_compliance_rate ?? 0)) },
            { name: "Resolution SLA %", data: engineers.map((item) => Number(item.resolution_compliance_rate ?? 0)) },
        ],
        labels: engineers.map((item) => item.engineer_name ?? "-"),
        colors: ["#0ea5e9", "#ef4444"],
    });

    renderChart("#eng-time-scatter-chart", {
        chart: { type: "scatter", height: 340, toolbar: { show: false }, zoom: { enabled: true } },
        series: [
            {
                name: "Engineer Time Profile",
                data: engineers.map((item) => [
                    Number(item.avg_response_minutes ?? 0),
                    Number(item.avg_resolution_minutes ?? 0),
                ]),
            },
        ],
        xaxis: { title: { text: "Avg Response Minutes" } },
        yaxis: { title: { text: "Avg Resolution Minutes" } },
        colors: ["#7c3aed"],
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const payload = window.operationsDashboardPayload ?? {};
    const page = payload?.page ?? "";

    if (page === "operations-dashboard") {
        renderOperationsDashboardCharts(payload);
    }

    if (page === "sla-performance") {
        renderSlaPerformanceCharts(payload);
    }

    if (page === "engineer-effectiveness") {
        renderEngineerEffectivenessCharts(payload);
    }
});
