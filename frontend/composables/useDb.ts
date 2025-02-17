import Dexie from 'dexie'
import type { Event } from '~/types/events'

class EventDatabase extends Dexie {
  events!: Dexie.Table<Event, number>
  pendingEvents!: Dexie.Table<Event, number>

  constructor() {
    super('event_db')
    
    this.version(1).stores({
      events: '++id, event_id, status, is_online',
      pendingEvents: '++id, event_id, status, is_online'
    })
  }

  async addEvent(event: Event) {
    return this.events.add(event)
  }

  async getEvent(eventId: string) {
    return this.events.where('event_id').equals(eventId).first()
  }

  async updateEvent(eventId: string, data: Partial<Event>) {
    return this.events.where('event_id').equals(eventId).modify(data)
  }

  async deleteEvent(eventId: string) {
    return this.events.where('event_id').equals(eventId).delete()
  }

  async getAllEvents() {
    return this.events.toArray()
  }

  async addPendingEvent(event: Event) {
    return this.pendingEvents.add(event)
  }

  async getPendingEvents() {
    return this.pendingEvents.toArray()
  }

  async clearPendingEvents() {
    return this.pendingEvents.clear()
  }

  async syncFromServer(events: Event[]) {
    return this.transaction('rw', this.events, async () => {
      await this.events.clear()
      await this.events.bulkAdd(events)
    })
  }
}

const db = new EventDatabase()

export const useDb = () => {
  return db
} 