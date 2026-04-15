import Link from "next/link";
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import type { ExchangeProject, Serial } from "@/lib/types";

interface Props {
  project: ExchangeProject;
  serials: Serial[];
}

export function ProjectCard({ project, serials }: Props) {
  const totalAvailable = serials.reduce((sum, s) => sum + s.quantity, 0);
  const vintages = serials
    .map((s) => {
      const parts = s.serial.split("_");
      return parts[parts.length - 1];
    })
    .filter((v, i, a) => a.indexOf(v) === i)
    .sort();

  return (
    <Card>
      <CardHeader className="pb-2">
        <div className="flex items-start justify-between">
          <CardTitle className="text-base">{project.name}</CardTitle>
          <Badge variant="outline" className="text-xs">
            {project.stage}
          </Badge>
        </div>
        <p className="text-xs text-muted-foreground">
          Project ID: {project.id} · {project.standard} ·{" "}
          {project.locations?.[0]?.country ?? "Unknown"}
        </p>
      </CardHeader>
      <CardContent className="pb-2">
        <div className="flex gap-6">
          <div className="text-center">
            <div className="text-lg font-bold">
              {totalAvailable.toLocaleString()}
            </div>
            <div className="text-xs uppercase text-muted-foreground">
              Available
            </div>
          </div>
          <div className="text-center">
            <div className="text-lg font-bold">{serials.length}</div>
            <div className="text-xs uppercase text-muted-foreground">
              Serials
            </div>
          </div>
          <div className="text-center">
            <div className="text-lg font-bold">
              {vintages.length > 1
                ? `${vintages[0]}-${vintages[vintages.length - 1]}`
                : vintages[0] ?? "-"}
            </div>
            <div className="text-xs uppercase text-muted-foreground">
              Vintages
            </div>
          </div>
        </div>
      </CardContent>
      <CardFooter className="gap-2">
        <Link
          href={`/projects/${project.id}`}
          className={cn(buttonVariants({ size: "sm" }))}
        >
          View Details →
        </Link>
        <Link
          href={`/retire?serial=${encodeURIComponent(serials[0]?.serial ?? "")}`}
          className={cn(buttonVariants({ variant: "outline", size: "sm" }))}
        >
          Retire Credits
        </Link>
      </CardFooter>
    </Card>
  );
}
