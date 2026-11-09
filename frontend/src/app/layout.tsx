import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "Mfklixo - Cloud Control Panel",
  description: "Next-Generation High Performance Linux Server Dashboard",
  icons: {
    icon: "/favicon.ico",
  },
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" className="dark">
      <body className="antialiased selection:bg-sky-500/30 selection:text-sky-200">
        {children}
      </body>
    </html>
  );
}
