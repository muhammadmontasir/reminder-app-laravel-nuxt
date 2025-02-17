export interface Event {
  id?: number
  event_id?: string
  title: string
  description: string
  start_time: string
  end_time: string
  status: string
  is_online: boolean
  reminder_time: string
  participants: string[]
  created_at?: string
  updated_at?: string
  deleted_at?: string | null
} 