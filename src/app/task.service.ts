import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Task } from './task';

@Injectable({
  providedIn: 'root',
})
export class TaskService {
  private apiUrl = 'http://localhost/kanban/pdo/api';

  constructor(private http: HttpClient) {}

  // Get ordered Tasks
  getTasks(): Observable<Task[]> {
    return this.http.get<Task[]>(`${this.apiUrl}/get_ordered_tasks`);
  }

  // Create a new task
  createTask(
    name: string,
    status: string,
    dueDate: string,
    isComplete: boolean = false
  ): Observable<any> {
    const data = {
      name,
      status,
      dueDate,
      isComplete,
    };
    return this.http.post(`${this.apiUrl}/create_task`, data);
  }

  //Update task order and status
  updateTask(task: Task): Observable<any> {
    const data = {
      id: task.id,
      order: task.order,
      status: task.status,
      isComplete: task.isComplete,
    };
    return this.http.post(`${this.apiUrl}/update_task_order`, data);
  }

  editTaskName(id: string, name: string, dueDate: string): Observable<any> {
    const data = {
      id: id,
      name: name,
      dueDate: dueDate, // Add dueDate to the data being sent
    };
    return this.http.post(`${this.apiUrl}/edit_task`, data);
  }

  deleteTask(id: string) {
    const data = {
      id: id,
    };
    return this.http.post(`${this.apiUrl}/delete_task`, data);
  }
}
