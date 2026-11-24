import Link from "next/link";
import { catalog, startTime } from "./events";

export default async function Home() {
  const events = await catalog();
  return <main><h1>Мероприятия</h1>
    {events === null ? <p role="alert">Не удалось загрузить мероприятия.</p> :
      events.length === 0 ? <p>Пока нет опубликованных мероприятий.</p> :
        <ul className="catalog">{events.map(event =>
          <li key={event.id}>
            <div><time dateTime={event.startsAt}>{startTime(event)}</time><span>{event.timezone}</span></div>
            <section><h2><Link href={`/events/${event.id}`}>{event.title}</Link></h2>
              <p>{event.city} · {event.venue}</p></section>
          </li>)}</ul>}
  </main>;
}
