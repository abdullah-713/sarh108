import { useEffect, useState, useRef } from 'react';
import { Bell, X, ExternalLink, Trophy, Award, Flame, AlertTriangle, PartyPopper, Clock, Megaphone } from 'lucide-react';

interface NewsItem {
    id: number;
    title: string;
    content?: string | null;
    type: string;
    type_name: string;
    type_color: string;
    type_icon: string;
    priority: string;
    icon?: string | null;
    color?: string | null;
    background_color?: string | null;
    action_url?: string | null;
    action_text?: string | null;
}

interface NewsTickerProps {
    branchId?: number;
    departmentId?: number;
    apiUrl?: string;
    autoFetch?: boolean;
    items?: NewsItem[];
    speed?: number;
    pauseOnHover?: boolean;
    className?: string;
}

const iconMap: Record<string, React.ComponentType<{ className?: string }>> = {
    announcement: Megaphone,
    achievement: Trophy,
    reminder: Clock,
    warning: AlertTriangle,
    celebration: PartyPopper,
    mvp: Trophy,
    badge: Award,
    streak: Flame,
    custom: Bell,
};

export default function NewsTicker({
    branchId,
    departmentId,
    apiUrl = '/api/news-ticker/active',
    autoFetch = true,
    items: initialItems = [],
    speed = 50,
    pauseOnHover = true,
    className = '',
}: NewsTickerProps) {
    const [items, setItems] = useState<NewsItem[]>(initialItems);
    const [isPaused, setIsPaused] = useState(false);
    const [expandedItem, setExpandedItem] = useState<NewsItem | null>(null);
    const tickerRef = useRef<HTMLDivElement>(null);

    // Fetch news items
    useEffect(() => {
        if (autoFetch) {
            fetchNews();
            const interval = setInterval(fetchNews, 60000); // Refresh every minute
            return () => clearInterval(interval);
        }
    }, [branchId, departmentId, autoFetch]);

    const fetchNews = async () => {
        try {
            const params = new URLSearchParams();
            if (branchId) params.append('branch_id', branchId.toString());
            if (departmentId) params.append('department_id', departmentId.toString());

            const response = await fetch(`${apiUrl}?${params.toString()}`);
            const data = await response.json();
            if (data.success) {
                setItems(data.data);
            }
        } catch (error) {
            console.error('Failed to fetch news:', error);
        }
    };

    const trackView = async (id: number) => {
        try {
            await fetch(`/api/news-ticker/${id}/view`, { method: 'POST' });
        } catch (error) {
            // Silent fail
        }
    };

    const trackClick = async (id: number) => {
        try {
            await fetch(`/api/news-ticker/${id}/click`, { method: 'POST' });
        } catch (error) {
            // Silent fail
        }
    };

    const handleItemClick = (item: NewsItem) => {
        trackView(item.id);
        if (item.content || item.action_url) {
            setExpandedItem(item);
        }
    };

    const handleActionClick = (item: NewsItem) => {
        if (item.action_url) {
            trackClick(item.id);
            window.open(item.action_url, '_blank');
        }
    };

    const getIcon = (type: string) => {
        const Icon = iconMap[type] || Bell;
        return <Icon className="w-4 h-4" />;
    };

    if (items.length === 0) {
        return null;
    }

    return (
        <>
            {/* Ticker Bar */}
            <div
                className={`relative overflow-hidden bg-gradient-to-r from-orange-500 to-orange-600 text-white ${className}`}
                onMouseEnter={() => pauseOnHover && setIsPaused(true)}
                onMouseLeave={() => pauseOnHover && setIsPaused(false)}
            >
                <div className="flex items-center">
                    {/* Icon */}
                    <div className="flex-shrink-0 px-4 py-2 bg-orange-600">
                        <Bell className="w-5 h-5" />
                    </div>

                    {/* Scrolling Content */}
                    <div className="flex-1 overflow-hidden">
                        <div
                            ref={tickerRef}
                            className="flex gap-8 animate-scroll whitespace-nowrap py-2"
                            style={{
                                animationPlayState: isPaused ? 'paused' : 'running',
                                animationDuration: `${items.length * speed}s`,
                            }}
                        >
                            {/* Duplicate items for seamless loop */}
                            {[...items, ...items].map((item, index) => (
                                <button
                                    key={`${item.id}-${index}`}
                                    onClick={() => handleItemClick(item)}
                                    className="flex items-center gap-2 hover:bg-orange-400/30 px-3 py-1 rounded transition-colors cursor-pointer"
                                >
                                    {getIcon(item.type)}
                                    <span className="font-medium">{item.title}</span>
                                    {item.priority === 'urgent' && (
                                        <span className="px-2 py-0.5 bg-red-500 rounded text-xs font-bold">
                                            عاجل
                                        </span>
                                    )}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {/* Expanded Item Modal */}
            {expandedItem && (
                <div 
                    className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
                    onClick={() => setExpandedItem(null)}
                >
                    <div 
                        className="bg-white rounded-xl max-w-md w-full shadow-2xl overflow-hidden"
                        onClick={(e) => e.stopPropagation()}
                    >
                        {/* Header */}
                        <div 
                            className="p-4 flex items-center justify-between"
                            style={{ backgroundColor: expandedItem.color || '#ff8531' }}
                        >
                            <div className="flex items-center gap-3 text-white">
                                {getIcon(expandedItem.type)}
                                <span className="font-bold">{expandedItem.type_name}</span>
                            </div>
                            <button
                                onClick={() => setExpandedItem(null)}
                                className="text-white/80 hover:text-white"
                            >
                                <X className="w-5 h-5" />
                            </button>
                        </div>

                        {/* Content */}
                        <div className="p-6">
                            <h3 className="text-xl font-bold text-gray-900 mb-3">
                                {expandedItem.title}
                            </h3>
                            {expandedItem.content && (
                                <p className="text-gray-600 leading-relaxed">
                                    {expandedItem.content}
                                </p>
                            )}
                        </div>

                        {/* Action */}
                        {expandedItem.action_url && (
                            <div className="px-6 pb-6">
                                <button
                                    onClick={() => handleActionClick(expandedItem)}
                                    className="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium flex items-center justify-center gap-2 transition-colors"
                                >
                                    <span>{expandedItem.action_text || 'عرض المزيد'}</span>
                                    <ExternalLink className="w-4 h-4" />
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            )}

            {/* Scroll Animation */}
            <style>{`
                @keyframes scroll {
                    0% {
                        transform: translateX(0);
                    }
                    100% {
                        transform: translateX(-50%);
                    }
                }
                .animate-scroll {
                    animation: scroll linear infinite;
                }
            `}</style>
        </>
    );
}

// Compact variant for sidebar or small spaces
interface CompactNewsProps {
    items: NewsItem[];
    maxItems?: number;
    className?: string;
}

export function CompactNews({ items, maxItems = 5, className = '' }: CompactNewsProps) {
    const displayItems = items.slice(0, maxItems);

    if (displayItems.length === 0) {
        return null;
    }

    return (
        <div className={`space-y-2 ${className}`}>
            {displayItems.map((item) => {
                const Icon = iconMap[item.type] || Bell;
                return (
                    <div
                        key={item.id}
                        className="flex items-start gap-3 p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer"
                    >
                        <div
                            className="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                            style={{ 
                                backgroundColor: item.background_color || '#fff7ed',
                                color: item.color || '#ff8531'
                            }}
                        >
                            <Icon className="w-4 h-4" />
                        </div>
                        <div className="flex-1 min-w-0">
                            <p className="font-medium text-sm text-gray-900 truncate">
                                {item.title}
                            </p>
                            {item.content && (
                                <p className="text-xs text-gray-500 mt-0.5 line-clamp-2">
                                    {item.content}
                                </p>
                            )}
                        </div>
                        {item.priority === 'urgent' && (
                            <span className="px-2 py-0.5 bg-red-100 text-red-600 rounded text-xs font-bold">
                                عاجل
                            </span>
                        )}
                    </div>
                );
            })}
        </div>
    );
}
