import { Status } from './enums';

export type UserRole = {
    id: string;
    name: string;
    guard_name: string;
};

export type UserPermission = {
    id: string;
    name: string;
    guard_name: string;
};

export type User = {
    id: string;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    status: Status | string | null;
    two_factor_enabled?: boolean;
    roles?: UserRole[];
    permissions?: UserPermission[];
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
