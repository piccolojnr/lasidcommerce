export interface AdminSetting {
    id: number;
    key: string;
    value: string | null;
    type: string | null;
    group: string | null;
}

export interface AdminSettingsGroup {
    group: string;
    items: AdminSetting[];
}
