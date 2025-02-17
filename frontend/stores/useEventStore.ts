import { defineStore } from 'pinia'
import type { Event } from '~/types/events'

export const useEventStore = defineStore('events', () => {
  const api = useApi()
  const db = useDb()
  const { isOnline } = useNetworkStatus()
  const toast = useToast()

  const events = ref<Event[]>([])
  const loading = ref(false)

  const fetchEvents = async () => {
    try {
      loading.value = true
      if (isOnline.value) {
        const response = await api.get('/events')
        events.value = response.data
        await db.syncFromServer(response.data)
      } else {
        events.value = await db.getAllEvents()
      }
    } catch (error) {
      toast.error('Failed to fetch events')
    } finally {
      loading.value = false
    }
  }

  const createEvent = async (eventData: Omit<Event, 'event_id'>) => {
    if (isOnline.value) {
      const response = await api.post('/events', eventData)
      await db.addEvent(response.data)
      await fetchEvents()
      return response.data
    } else {
      const event = {
        ...eventData,
        is_online: false,
        status: 'upcoming'
      }
      await db.addPendingEvent(event)
      await fetchEvents()
      return event
    }
  }

  const updateEvent = async (eventId: string, eventData: Partial<Event>) => {
    if (isOnline.value) {
      const response = await api.put(`/events/${eventId}`, eventData)
      await db.updateEvent(eventId, response.data)
      await fetchEvents()
      return response.data
    } else {
      const event = await db.getEvent(eventId)
      if (!event) throw new Error('Event not found')
      
      const updatedEvent = {
        ...event,
        ...eventData,
        is_online: false
      }
      await db.addPendingEvent(updatedEvent)
      await fetchEvents()
      return updatedEvent
    }
  }

  const deleteEvent = async (eventId: string) => {
    if (isOnline.value) {
      await api.delete(`/events/${eventId}`)
      await db.deleteEvent(eventId)
      await fetchEvents()
    } else {
      await db.addPendingEvent({
        event_id: eventId,
        _deleted: true
      } as any)
      await fetchEvents()
    }
  }

  const syncEvents = async () => {
    if (!isOnline.value) {
      toast.error('No internet connection')
      return
    }

    const pendingEvents = await db.getPendingEvents()
    if (pendingEvents.length === 0) {
      toast.error('No events to sync')
      return
    }

    await api.post('/events/sync', { events: pendingEvents })
    await db.clearPendingEvents()
    await fetchEvents()
  }

  return {
    events,
    loading,
    fetchEvents,
    createEvent,
    updateEvent,
    deleteEvent,
    syncEvents
  }
}) 