export interface DashboardStatCard {
    label: string;
    value: string | number;
    description?: string;
}

export interface DashboardSummary {
    stats: DashboardStatCard[];
}
