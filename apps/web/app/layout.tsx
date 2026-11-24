import type { ReactNode } from "react";
import "./globals.css";
import Link from "next/link";

export const metadata = { title: "EventHub | Мероприятия" };

export default function RootLayout({ children }: { children: ReactNode }) {
  return <html lang="ru"><body>
    <header><Link href="/">EventHub</Link></header>
    {children}
  </body></html>;
}
