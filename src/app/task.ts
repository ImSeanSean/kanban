export interface Task {
  id: string;
  name: string;
  dueDate: string;
  isComplete: boolean;
  order: number;
  status: string;
  isEditing: boolean;
}
