import "server-only";

export type EventItem = {
  id: string; title: string; city: string; venue: string; description: string;
  startsAt: string; timezone: string; program: string[]; speakers: string[];
};

async function read(path: string) {
  return fetch(`${process.env.API_BASE_URL}/api/v1/events${path}`, {
    cache: "no-store", signal: AbortSignal.timeout(5000),
  }).catch(() => null);
}

export async function catalog(): Promise<EventItem[] | null> {
  const response = await read("");
  return response?.ok ? response.json() : null;
}

export async function detail(id: string): Promise<EventItem | null | "missing"> {
  const response = await read(`/${encodeURIComponent(id)}`);
  if (response?.status === 404) return "missing";
  return response?.ok ? response.json() : null;
}

export function startTime(event: EventItem) {
  return new Intl.DateTimeFormat("ru-RU", {
    timeZone: event.timezone, dateStyle: "long", timeStyle: "short",
  }).format(new Date(event.startsAt));
}
