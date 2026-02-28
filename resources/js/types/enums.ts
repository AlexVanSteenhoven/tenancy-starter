export enum Role {
    OWNER = 'owner',
    ADMIN = 'admin',
    MEMBER = 'member',
}

export const roleTranslationMap: Record<Role, string> = {
    [Role.OWNER]: 'roles.owner',
    [Role.ADMIN]: 'roles.admin',
    [Role.MEMBER]: 'roles.member',
};

export enum Status {
    ACTIVE = 'active',
    INACTIVE = 'inactive',
    PENDING = 'pending',
    BLOCKED = 'blocked',
    SUSPENDED = 'suspended',
    DELETED = 'deleted',
    ARCHIVED = 'archived',
    VERIFIED = 'verified',
    UNVERIFIED = 'unverified',
}

export const statusTranslationMap: Record<Status, string> = {
    [Status.ACTIVE]: 'status.active',
    [Status.INACTIVE]: 'status.inactive',
    [Status.PENDING]: 'status.pending',
    [Status.BLOCKED]: 'status.blocked',
    [Status.SUSPENDED]: 'status.suspended',
    [Status.DELETED]: 'status.deleted',
    [Status.ARCHIVED]: 'status.archived',
    [Status.VERIFIED]: 'status.verified',
    [Status.UNVERIFIED]: 'status.unverified',
};
