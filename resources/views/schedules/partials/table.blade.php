<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-bordered table-hover mb-0 align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th width="10%">Horário</th>
                    <th width="25%">Programa</th>
                    <th width="20%">Blocos (ID)</th>
                    <th width="10%">Mago</th>
                    <th width="10%">Verif.</th>
                    <th>Obs</th>
                    <th width="10%">Ações</th> </tr>
            </thead>
            <tbody>
                @forelse($grade as $item)
                <tr class="{{ $item->status_mago && $item->status_verification ? 'row-ok' : '' }}">
                    <td class="fw-bold fs-5">
                        {{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }}
                        <div class="small text-muted fw-normal">{{ $item->duration }} min</div>
                    </td>
                    <td class="text-start fw-bold text-uppercase">{{ $item->program->name }}</td>
                    
                    <td>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control border-0 bg-transparent text-center" 
                                   value="{{ $item->custom_info }}" readonly 
                                   title="{{ $item->custom_info }}">
                        </div>
                    </td>

                    <td>
                        <button onclick="toggleStatus(this, {{ $item->id }}, 'mago')" 
                                class="btn btn-secondary w-100 btn-sm btn-mago {{ $item->status_mago ? 'btn-toggle-on' : 'btn-toggle-off' }}">
                            {{ $item->status_mago ? 'OK' : 'Pendente' }}
                        </button>
                    </td>

                    <td>
                        <button onclick="toggleStatus(this, {{ $item->id }}, 'verification')" 
                                class="btn btn-secondary w-100 btn-sm btn-verif {{ $item->status_verification ? 'btn-toggle-on' : 'btn-toggle-off' }}">
                            {{ $item->status_verification ? 'OK' : 'Pendente' }}
                        </button>
                    </td>

                    <td class="text-start text-danger fw-bold small">
                        {{ $item->notes ?? ' ' }}
                    </td>

                    <td>
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-primary"
                                onclick="openEditModal(this)"
                                data-id="{{ $item->id }}"
                                data-date="{{ \Carbon\Carbon::parse($item->date)->format('Y-m-d') }}"
                                data-program-id="{{ $item->program_id }}"
                                data-start-time="{{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }}"
                                data-duration="{{ $item->duration }}"
                                data-custom-info="{{ $item->custom_info }}"
                                data-notes="{{ $item->notes }}">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <form action="{{ route('schedules.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Tem certeza?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-5 text-muted">Grade vazia. Clique em "Clonar Anterior" ou adicione manualmente.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>