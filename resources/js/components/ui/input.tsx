import * as React from "react"

import { cn } from "@/lib/utils"

function Input({ className, type, ...props }: React.ComponentProps<"input">) {
  return (
    <input
      type={type}
      data-slot="input"
      className={cn(
        "flex h-11 w-full min-w-0 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-base shadow-sm transition-all duration-200 outline-none",
        "placeholder:text-gray-400 dark:placeholder:text-gray-500",
        "file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground",
        "focus:border-orange-500 focus:ring-4 focus:ring-orange-500/15 dark:focus:ring-orange-500/25",
        "hover:border-gray-300 dark:hover:border-gray-600",
        "disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-gray-800",
        "aria-invalid:border-red-500 aria-invalid:ring-red-500/20",
        "sm:h-10 sm:text-sm",
        className
      )}
      {...props}
    />
  )
}

export { Input }
