import { Loader2Icon } from "lucide-react"
import { useTranslation } from "react-i18next"

import { cn } from "@/lib/utils"
import "@lib/i18n"

function Spinner({ className, ...props }: React.ComponentProps<"svg">) {
  const { t } = useTranslation()

  return (
    <Loader2Icon
      role="status"
      aria-label={t("app.ui.loading")}
      className={cn("size-4 animate-spin", className)}
      {...props}
    />
  )
}

export { Spinner }
