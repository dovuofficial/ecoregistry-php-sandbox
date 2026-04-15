import type { Metadata } from "next";
import { Inter } from "next/font/google";
import "./globals.css";
import { NavBar } from "@/components/nav-bar";
import { AccountProvider } from "@/lib/account-context";

const inter = Inter({ subsets: ["latin"] });

export const metadata: Metadata = {
  title: "EcoRegistry Explorer",
  description: "Debug and demo tool for EcoRegistry API",
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en">
      <body className={`${inter.className} bg-slate-50`}>
        <AccountProvider>
          <NavBar />
          {children}
        </AccountProvider>
      </body>
    </html>
  );
}
