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
    DELETED = 'deleted',
    INACTIVE = 'inactive',
    BLOCKED = 'blocked',
    PENDING = 'pending',
    SUSPENDED = 'suspended',
}

export const statusTranslationMap: Record<Status, string> = {
    [Status.ACTIVE]: 'status.active',
    [Status.DELETED]: 'status.deleted',
    [Status.INACTIVE]: 'status.inactive',
    [Status.BLOCKED]: 'status.blocked',
    [Status.PENDING]: 'status.pending',
    [Status.SUSPENDED]: 'status.suspended',
};
