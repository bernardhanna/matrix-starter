# Tests doc (moved)

The developer manual lives at **[MANUAL-QA.md](./MANUAL-QA.md)**.

Hourly shop report: Matrix Site Monitor emails **bernard@matrixinternet.ie** after each synthetic run.

```bash
wp msm shop-report --coverage
```

WooCommerce email recipients are **live-only** — see MANUAL-QA.md section 11. Do not assert them on localhost.
