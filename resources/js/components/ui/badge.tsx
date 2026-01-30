import * as React from "react"
import { Slot } from "@radix-ui/react-slot"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/lib/utils"

const badgeVariants = cva(
  "inline-flex items-center justify-center rounded-full border px-3 py-1 text-xs font-semibold w-fit whitespace-nowrap shrink-0 [&>svg]:size-3 gap-1.5 [&>svg]:pointer-events-none transition-all duration-200 overflow-hidden",
  {
    variants: {
      variant: {
        default:
          "border-transparent bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-sm shadow-orange-500/25 [a&]:hover:from-orange-600 [a&]:hover:to-orange-700",
        secondary:
          "border-transparent bg-gray-800 text-white [a&]:hover:bg-gray-700",
        destructive:
          "border-transparent bg-gradient-to-r from-red-500 to-red-600 text-white shadow-sm shadow-red-500/25 [a&]:hover:from-red-600 [a&]:hover:to-red-700",
        outline:
          "border-2 border-orange-500 text-orange-500 bg-transparent [a&]:hover:bg-orange-500 [a&]:hover:text-white",
        success:
          "border-transparent bg-gradient-to-r from-green-500 to-green-600 text-white shadow-sm shadow-green-500/25 [a&]:hover:from-green-600 [a&]:hover:to-green-700",
        warning:
          "border-transparent bg-gradient-to-r from-amber-500 to-amber-600 text-white shadow-sm shadow-amber-500/25 [a&]:hover:from-amber-600 [a&]:hover:to-amber-700",
        info:
          "border-transparent bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400",
        ghost:
          "border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
)

function Badge({
  className,
  variant,
  asChild = false,
  ...props
}: React.ComponentProps<"span"> &
  VariantProps<typeof badgeVariants> & { asChild?: boolean }) {
  const Comp = asChild ? Slot : "span"

  return (
    <Comp
      data-slot="badge"
      className={cn(badgeVariants({ variant }), className)}
      {...props}
    />
  )
}

export { Badge, badgeVariants }
