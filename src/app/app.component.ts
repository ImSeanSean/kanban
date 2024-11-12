import { Component, OnInit } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import {
  CdkDragDrop,
  CdkDropList,
  CdkDropListGroup,
  DragDropModule,
  moveItemInArray,
  transferArrayItem,
} from '@angular/cdk/drag-drop';
import { CommonModule } from '@angular/common';
import { TaskService } from './task.service';
import { FormsModule, NgModel } from '@angular/forms';
import { Task } from './task';
import { MatDialog } from '@angular/material/dialog';
import { InputDialogComponent } from './input-dialog/input-dialog.component';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [
    RouterOutlet,
    DragDropModule,
    CdkDropList,
    CommonModule,
    CdkDropListGroup,
    FormsModule,
  ],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css',
})
export class AppComponent implements OnInit {
  title = 'Kanban-B';

  todoTasks: Task[] = [];
  inProgressTasks: Task[] = [];
  doneTasks: Task[] = [];

  todoTaskName = '';
  inProgressTaskName = '';
  doneTaskName = '';

  constructor(private taskService: TaskService, private dialog: MatDialog) {} // Inject TaskService

  ngOnInit(): void {
    this.getOrderedTask();
  }

  getOrderedTask() {
    this.taskService.getTasks().subscribe((tasks: Task[]) => {
      this.todoTasks = tasks.filter((task) => task.status === 'todo');
      this.inProgressTasks = tasks.filter(
        (task) => task.status === 'inprogress'
      );
      this.doneTasks = tasks.filter((task) => task.status === 'done');
    });
  }

  createToDoTask() {
    const data = { name: this.todoTaskName, status: 'todo' };
    this.taskService
      .createTask(this.todoTaskName, 'todo', '', false)
      .subscribe(() => {
        this.getOrderedTask();
        this.todoTaskName = '';
      });
  }

  createInProgressTask() {
    this.taskService
      .createTask(this.inProgressTaskName, 'inprogress', '', false)
      .subscribe(() => {
        this.getOrderedTask();
        this.inProgressTaskName = '';
      });
  }

  createDoneTask() {
    this.taskService
      .createTask(this.doneTaskName, 'done', '', false)
      .subscribe(() => {
        this.getOrderedTask();
        this.doneTaskName = '';
      });
  }

  updateTaskOrder(tasks: Task[]) {
    tasks.forEach((task, index) => {
      task.order = index;
      this.taskService.updateTask(task).subscribe((result) => {
        console.log('Task order updated:', result);
      });
    });
  }

  deleteTask(id: string) {
    if (!id) {
      console.error('Task ID is missing!');
      return;
    }

    this.taskService.deleteTask(id).subscribe({
      next: (result) => {
        console.log('Delete result:', result);
        this.getOrderedTask(); // Refresh task list
      },
      error: (err) => console.error('Error deleting task:', err),
    });
  }

  drop(event: CdkDragDrop<Task[]>) {
    if (event.previousContainer === event.container) {
      moveItemInArray(
        event.container.data,
        event.previousIndex,
        event.currentIndex
      );
    } else {
      transferArrayItem(
        event.previousContainer.data,
        event.container.data,
        event.previousIndex,
        event.currentIndex
      );
    }

    this.updateTaskOrder(event.container.data);
    if (event.previousContainer !== event.container) {
      this.updateTaskOrder(event.previousContainer.data);
    }
  }

  editTask(task: Task) {
    const dialogRef = this.dialog.open(InputDialogComponent, {
      width: '400px',
      height: '350px',
      data: { task: { ...task } }, // Pass a copy of the task to avoid mutating the original directly
    });

    dialogRef.afterClosed().subscribe((updatedTask) => {
      if (updatedTask) {
        this.taskService
          .editTaskName(task.id, updatedTask.name, updatedTask.dueDate)
          .subscribe({
            next: (result) => {
              console.log('Task updated successfully:', result);

              // Optionally, update the UI by modifying the task's name and dueDate locally
              task.name = updatedTask.name;
              task.dueDate = updatedTask.dueDate;
              this.getOrderedTask();
            },
            error: (err) => {
              console.error('Error updating task:', err);
            },
          });
      }
    });
  }

  saveTask(task: Task) {
    task.isEditing = false; // Exit edit mode

    this.taskService.updateTask(task).subscribe({
      next: (result) => {
        console.log('Task updated successfully:', result);
      },
      error: (err) => {
        console.error('Error updating task:', err);
        // Optionally revert changes in case of an error
      },
    });
  }

  trackByFn(index: number, item: Task): string {
    return item.id; // Use a unique property (like `id`) as the tracking identifier
  }
}
