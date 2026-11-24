import Link from "next/link";
import { notFound } from "next/navigation";
import { detail, startTime } from "../../events";

export default async function EventPage({ params }: { params: Promise<{ id: string }> }) {
  const event = await detail((await params).id);
  if (event === "missing") notFound();
  if (!event) return <main><p role="alert">Не удалось загрузить мероприятие.</p></main>;
  return <main>
    <Link href="/">Все мероприятия</Link>
    <h1>{event.title}</h1><p>{event.description}</p>
    <p><time dateTime={event.startsAt}>{startTime(event)}</time> ({event.timezone})</p>
    <p>{event.city} · {event.venue}</p>
    <h2>Программа</h2><ol>{event.program.map((item, i) => <li key={i}>{item}</li>)}</ol>
    <h2>Спикеры</h2><ul>{event.speakers.map((name, i) => <li key={i}>{name}</li>)}</ul>
  </main>;
}
