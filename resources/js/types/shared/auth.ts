export interface AuthUser {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    phone?: string | null;
    status?: string;
    permissions?: string[];
    roles?: string[];
}

export interface AuthState {
    user: AuthUser | null;
    permissions?: string[];
}
