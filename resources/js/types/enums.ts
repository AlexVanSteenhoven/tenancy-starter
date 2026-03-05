export enum Role {
    OWNER = 'owner',
    ADMIN = 'admin',
    MEMBER = 'member',
}

export const roleTranslationMap: Record<Role, string> = {
    [Role.OWNER]: 'app.enums.role.owner',
    [Role.ADMIN]: 'app.enums.role.admin',
    [Role.MEMBER]: 'app.enums.role.member',
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
    [Status.ACTIVE]: 'app.enums.status.active',
    [Status.DELETED]: 'app.enums.status.deleted',
    [Status.INACTIVE]: 'app.enums.status.inactive',
    [Status.BLOCKED]: 'app.enums.status.blocked',
    [Status.PENDING]: 'app.enums.status.pending',
    [Status.SUSPENDED]: 'app.enums.status.suspended',
};
