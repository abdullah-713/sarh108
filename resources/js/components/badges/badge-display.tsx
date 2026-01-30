import { Award, Star, Flame, Clock, Trophy, UserCheck, Users, Sparkles } from 'lucide-react';

interface BadgeDisplayProps {
    badge: {
        id: number;
        name: string;
        name_ar: string;
        description?: string | null;
        description_ar?: string | null;
        icon?: string | null;
        color?: string | null;
        background_color?: string | null;
        tier: string;
        tier_name: string;
        tier_color: string;
        type: string;
        type_name: string;
        points: number;
    };
    size?: 'sm' | 'md' | 'lg';
    showDetails?: boolean;
    className?: string;
}

const iconMap: Record<string, React.ComponentType<{ className?: string }>> = {
    punctuality: Clock,
    attendance: UserCheck,
    early_bird: Star,
    streak: Flame,
    perfect_month: Trophy,
    mvp: Award,
    team_player: Users,
    custom: Sparkles,
};

const sizeClasses = {
    sm: {
        container: 'w-10 h-10',
        icon: 'w-5 h-5',
        ring: 'ring-2',
    },
    md: {
        container: 'w-14 h-14',
        icon: 'w-7 h-7',
        ring: 'ring-3',
    },
    lg: {
        container: 'w-20 h-20',
        icon: 'w-10 h-10',
        ring: 'ring-4',
    },
};

export default function BadgeDisplay({ 
    badge, 
    size = 'md', 
    showDetails = false,
    className = '' 
}: BadgeDisplayProps) {
    const Icon = iconMap[badge.type] || Award;
    const sizeConfig = sizeClasses[size];

    return (
        <div className={`inline-flex flex-col items-center gap-2 ${className}`}>
            {/* Badge Icon */}
            <div
                className={`
                    ${sizeConfig.container} 
                    rounded-full 
                    flex items-center justify-center 
                    ${sizeConfig.ring}
                    transition-transform hover:scale-110
                `}
                style={{
                    backgroundColor: badge.background_color || '#fff7ed',
                    color: badge.color || '#ff8531',
                    borderColor: badge.tier_color,
                    boxShadow: `0 0 0 2px ${badge.tier_color}40`,
                }}
                title={`${badge.name_ar} - ${badge.tier_name}`}
            >
                <Icon className={sizeConfig.icon} />
            </div>

            {/* Badge Details */}
            {showDetails && (
                <div className="text-center">
                    <p className="font-semibold text-sm text-gray-900">{badge.name_ar}</p>
                    <p 
                        className="text-xs font-medium"
                        style={{ color: badge.tier_color }}
                    >
                        {badge.tier_name}
                    </p>
                    {badge.description_ar && (
                        <p className="text-xs text-gray-500 mt-1 max-w-[150px]">
                            {badge.description_ar}
                        </p>
                    )}
                    <div className="flex items-center justify-center gap-1 mt-1 text-xs text-orange-600">
                        <Star className="w-3 h-3" />
                        <span>{badge.points} نقطة</span>
                    </div>
                </div>
            )}
        </div>
    );
}

// Component to display multiple badges
interface BadgeListProps {
    badges: BadgeDisplayProps['badge'][];
    size?: 'sm' | 'md' | 'lg';
    maxDisplay?: number;
    showTooltip?: boolean;
}

export function BadgeList({ 
    badges, 
    size = 'sm', 
    maxDisplay = 5,
    showTooltip = true 
}: BadgeListProps) {
    const displayBadges = badges.slice(0, maxDisplay);
    const remaining = badges.length - maxDisplay;

    return (
        <div className="flex items-center gap-1 flex-wrap">
            {displayBadges.map((badge) => (
                <BadgeDisplay 
                    key={badge.id} 
                    badge={badge} 
                    size={size}
                    showDetails={false}
                />
            ))}
            {remaining > 0 && (
                <div 
                    className="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-600"
                    title={`${remaining} شارات أخرى`}
                >
                    +{remaining}
                </div>
            )}
        </div>
    );
}

// Earned badge with date
interface EarnedBadgeProps {
    badge: BadgeDisplayProps['badge'];
    earnedAt: string;
    reason?: string | null;
}

export function EarnedBadge({ badge, earnedAt, reason }: EarnedBadgeProps) {
    return (
        <div className="flex items-center gap-3 p-3 rounded-lg bg-gradient-to-r from-orange-50 to-yellow-50 border border-orange-100">
            <BadgeDisplay badge={badge} size="md" />
            <div className="flex-1">
                <p className="font-semibold text-gray-900">{badge.name_ar}</p>
                <p className="text-xs text-gray-500">{badge.tier_name}</p>
                {reason && (
                    <p className="text-sm text-gray-600 mt-1">{reason}</p>
                )}
                <p className="text-xs text-gray-400 mt-1">
                    {new Date(earnedAt).toLocaleDateString('ar-SA')}
                </p>
            </div>
            <div className="text-center">
                <span className="text-2xl font-bold text-orange-500">{badge.points}</span>
                <p className="text-xs text-gray-500">نقطة</p>
            </div>
        </div>
    );
}
