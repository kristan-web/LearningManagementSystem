<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExcessiveAbsenceAlert extends Notification
{
    use Queueable;

    protected Student $student;
    protected int $absenceCount;
    protected string $subjectName;

    /**
     * Create a new notification instance.
     */
    public function __construct(Student $student, int $absenceCount, string $subjectName)
    {
        $this->student = $student;
        $this->absenceCount = $absenceCount;
        $this->subjectName = $subjectName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $studentName = $this->student->user->name ?? 'Student';
        $guardianName = $notifiable->name ?? 'Guardian';

        return (new MailMessage)
            ->subject("Excessive Absence Alert: {$studentName}")
            ->greeting("Hello {$guardianName},")
            ->line("This is to inform you that {$studentName} has accumulated {$this->absenceCount} unexcused absences or late arrivals in the past 30 days.")
            ->line("Subject of concern: {$this->subjectName}")
            ->line("Regular attendance is crucial for academic success. Please discuss the importance of attending classes with {$studentName}.")
            ->action('View Attendance Record', url('/student/attendance'))
            ->line('If you believe there is an error in this report, please contact the school administration immediately.')
            ->salutation('Sincerely,', config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'student_id' => $this->student->student_id,
            'student_name' => $this->student->user->name,
            'absence_count' => $this->absenceCount,
            'subject_name' => $this->subjectName,
            'timestamp' => now()->toISOString(),
        ];
    }
}