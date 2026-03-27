import ApexCharts from "apexcharts";

window.ApexCharts = ApexCharts;

function renderChart(selector, options) {
    const element = document.querySelector(selector);
    if (!element) {
        return;
    }

    try {
        element.innerHTML = "";
        const chart = new ApexCharts(element, options);
        chart.render();
    } catch (error) {
        console.error(`Failed to render chart for ${selector}`, error);
    }
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
    const trendCategories = trend.map((item) =>
        new Date(item.date ?? "").toLocaleDateString("id-ID", { day: "2-digit", month: "short" }),
    );
    const backlogPressure = [];
    let rollingBacklog = 0;

    trend.forEach((item) => {
        rollingBacklog = Math.max(0, rollingBacklog + Number(item.created ?? 0) - Number(item.completed ?? 0));
        backlogPressure.push(rollingBacklog);
    });

    const peakCreated = trend.reduce(
        (carry, item, index) => {
            const value = Number(item.created ?? 0);
            return value > carry.value ? { value, index } : carry;
        },
        { value: 0, index: -1 },
    );

    renderChart("#ops-daily-trend-chart", {
        chart: {
            type: "area",
            height: 250,
            toolbar: { show: false },
        },
        stroke: { curve: "smooth", width: 3 },
        series: [
            {
                name: "Demand",
                data: trend.map((item) => Number(item.created ?? 0)),
            },
            {
                name: "Completed",
                data: trend.map((item) => Number(item.completed ?? 0)),
            },
        ],
        xaxis: {
            categories: trendCategories,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                rotate: 0,
                trim: true,
                style: {
                    colors: "#64748b",
                    fontSize: "12px",
                },
            },
        },
        yaxis: {
            min: 0,
            tickAmount: 4,
            labels: {
                style: {
                    colors: "#64748b",
                    fontSize: "12px",
                },
            },
            title: {
                text: "Ticket Count",
                style: {
                    color: "#64748b",
                    fontSize: "12px",
                    fontWeight: 600,
                },
            },
        },
        colors: ["#2563eb", "#0f766e"],
        fill: {
            type: "gradient",
            opacity: 0.18,
            gradient: {
                shadeIntensity: 0.3,
                opacityFrom: 0.28,
                opacityTo: 0.04,
                stops: [0, 100],
            },
        },
        markers: {
            size: 4,
            strokeWidth: 2,
            strokeColors: "#ffffff",
            hover: { sizeOffset: 2 },
        },
        grid: {
            borderColor: "#e2e8f0",
            strokeDashArray: 4,
            padding: {
                left: 8,
                right: 12,
            },
        },
        legend: {
            position: "top",
            horizontalAlign: "left",
            fontSize: "13px",
            labels: {
                colors: "#475569",
            },
            markers: {
                radius: 12,
            },
        },
        tooltip: {
            shared: true,
            intersect: false,
            y: {
                formatter: function (value) {
                    return `${Number(value).toFixed(0)} tickets`;
                },
            },
        },
        annotations: peakCreated.index >= 0
            ? {
                  xaxis: [
                      {
                          x: trendCategories[peakCreated.index],
                          borderColor: "#f59e0b",
                          strokeDashArray: 3,
                          label: {
                              borderColor: "#f59e0b",
                              style: {
                                  color: "#fff",
                                  background: "#f59e0b",
                                  fontSize: "11px",
                                  fontWeight: 600,
                              },
                              text: `Peak demand ${peakCreated.value}`,
                          },
                      },
                  ],
              }
            : {},
        dataLabels: { enabled: false },
    });

    renderChart("#ops-sla-response-chart", {
        chart: { type: "donut", height: 220 },
        labels: ["On Time", "Breached", "Pending"],
        series: buildSeriesFromSummary(sla.response),
        colors: ["#16a34a", "#dc3545", "#f59e0b"],
        legend: { position: "bottom" },
    });

    renderChart("#ops-sla-resolution-chart", {
        chart: { type: "donut", height: 220 },
        labels: ["On Time", "Breached", "Pending"],
        series: buildSeriesFromSummary(sla.resolution),
        colors: ["#0ea5e9", "#ef4444", "#f59e0b"],
        legend: { position: "bottom" },
    });

    renderChart("#ops-inspection-result-chart", {
        chart: { type: "radialBar", height: 170, sparkline: { enabled: true } },
        series: [inspection.normal_rate ?? 0],
        labels: ["Normal Result Rate"],
        colors: ["#2563eb"],
        plotOptions: {
            radialBar: {
                hollow: { size: "66%" },
                track: {
                    background: "#e8eef7",
                    strokeWidth: "100%",
                    margin: 6,
                },
                dataLabels: {
                    name: {
                        offsetY: -12,
                        fontSize: "13px",
                        color: "#334155",
                    },
                    value: {
                        offsetY: 10,
                        fontSize: "24px",
                        fontWeight: 700,
                        formatter: function (val) {
                            return `${Number(val).toFixed(2)}%`;
                        },
                    },
                },
            },
        },
    });

    renderChart("#ops-top-engineer-score-chart", {
        chart: { type: "bar", height: 160, toolbar: { show: false } },
        series: [
            {
                name: "Effectiveness Score",
                data: topEngineers.map((item) => Number(item.effectiveness_score ?? 0)),
            },
        ],
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 8,
                barHeight: "48%",
            },
        },
        xaxis: {
            categories: topEngineers.map((item) => item.engineer_name ?? "-"),
            max: 100,
            labels: {
                style: {
                    colors: "#64748b",
                    fontSize: "12px",
                },
            },
        },
        yaxis: {
            labels: {
                style: {
                    colors: "#334155",
                    fontSize: "12px",
                    fontWeight: 600,
                },
            },
        },
        grid: {
            borderColor: "#e2e8f0",
            strokeDashArray: 4,
        },
        colors: ["#2563eb"],
        legend: { show: false },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return Number(val).toFixed(1);
            },
            style: {
                colors: ["#334155"],
                fontWeight: 700,
            },
            offsetX: 8,
        },
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

function renderExecutiveReportCharts(payload) {
    const data = payload?.executiveReport ?? {};
    const current = data?.current ?? {};
    const comparisons = data?.comparisons ?? [];

    if (!comparisons.length) {
        return;
    }

    const benchmarkCategories = ["Completion Rate", "Response SLA", "Resolution SLA", "Engineer Score"];

    const pressureCategories = ["Periode Aktif", ...comparisons.map((comparison) => comparison?.label ?? "Comparison")];

    renderChart("#executive-report-quality-chart", {
        chart: { type: "bar", height: 340, toolbar: { show: false } },
        plotOptions: {
            bar: {
                borderRadius: 6,
                columnWidth: "48%",
            },
        },
        series: [
            {
                name: "Current",
                data: [
                    Number(current?.derived?.completion_rate ?? 0),
                    Number(current?.sla?.response?.compliance_rate ?? 0),
                    Number(current?.sla?.resolution?.compliance_rate ?? 0),
                    Number(current?.engineer?.avg_effectiveness_score ?? 0),
                ],
            },
            ...comparisons.map((comparison) => ({
                name: comparison?.label ?? "Comparison",
                data: [
                    Number(comparison?.snapshot?.derived?.completion_rate ?? 0),
                    Number(comparison?.snapshot?.sla?.response?.compliance_rate ?? 0),
                    Number(comparison?.snapshot?.sla?.resolution?.compliance_rate ?? 0),
                    Number(comparison?.snapshot?.engineer?.avg_effectiveness_score ?? 0),
                ],
            })),
        ],
        xaxis: { categories: benchmarkCategories },
        colors: ["#2563eb", "#0ea5e9", "#f59e0b", "#8b5cf6"],
        legend: { position: "top" },
        dataLabels: { enabled: false },
        yaxis: {
            labels: {
                formatter: function (value) {
                    return Number(value).toFixed(0);
                },
            },
        },
    });

    renderChart("#executive-report-pressure-chart", {
        chart: { type: "bar", height: 340, stacked: false, toolbar: { show: false } },
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 6,
                barHeight: "52%",
            },
        },
        series: [
            {
                name: "Ticket Volume",
                data: [
                    Number(current?.summary?.total_tickets ?? 0),
                    ...comparisons.map((comparison) => Number(comparison?.snapshot?.summary?.total_tickets ?? 0)),
                ],
            },
            {
                name: "Overdue Resolution",
                data: [
                    Number(current?.summary?.overdue_resolution_tickets ?? 0),
                    ...comparisons.map((comparison) => Number(comparison?.snapshot?.summary?.overdue_resolution_tickets ?? 0)),
                ],
            },
            {
                name: "Unassigned",
                data: [
                    Number(current?.summary?.unassigned_tickets ?? 0),
                    ...comparisons.map((comparison) => Number(comparison?.snapshot?.summary?.unassigned_tickets ?? 0)),
                ],
            },
            {
                name: "Abnormal Inspection",
                data: [
                    Number(current?.inspection?.abnormal_inspections ?? 0),
                    ...comparisons.map((comparison) => Number(comparison?.snapshot?.inspection?.abnormal_inspections ?? 0)),
                ],
            },
        ],
        xaxis: {
            categories: pressureCategories,
        },
        colors: ["#2563eb", "#ef4444", "#f59e0b", "#06b6d4"],
        legend: { position: "top" },
        dataLabels: { enabled: false },
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

    if (page === "executive-report") {
        renderExecutiveReportCharts(payload);
    }
});
