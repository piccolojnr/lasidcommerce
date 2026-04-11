export interface AuthUser {
    id: number;
    name: string;
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
