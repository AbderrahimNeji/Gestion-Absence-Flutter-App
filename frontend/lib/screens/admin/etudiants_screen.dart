import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class EtudiantsScreen extends StatefulWidget {
  const EtudiantsScreen({super.key});

  @override
  State<EtudiantsScreen> createState() => _EtudiantsScreenState();
}

class _EtudiantsScreenState extends State<EtudiantsScreen> {
  late Future<List<dynamic>> _futureEtudiants;
  List<dynamic> _classes = [];
  final TextEditingController _searchCtrl = TextEditingController();
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _futureEtudiants = ApiService.getEtudiants();
    _loadClasses();
  }

  void _refresh() {
    setState(() {
      _futureEtudiants = ApiService.getEtudiants();
    });
  }

  Future<void> _loadClasses() async {
    _classes = await ApiService.getClasses();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }
  void _showAddDialog() {
    final nomCtrl = TextEditingController();
    final prenomCtrl = TextEditingController();
    final emailCtrl = TextEditingController();
    final passwordCtrl = TextEditingController();
    String? selectedClasseId;

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Ajouter un étudiant'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: nomCtrl,
                decoration: const InputDecoration(labelText: 'Nom'),
              ),
              TextField(
                controller: prenomCtrl,
                decoration: const InputDecoration(labelText: 'Prénom'),
              ),
              TextField(
                controller: emailCtrl,
                decoration: const InputDecoration(labelText: 'Email'),
              ),
              TextField(
                controller: passwordCtrl,
                obscureText: true,
                decoration: const InputDecoration(labelText: 'Mot de passe'),
              ),
              const SizedBox(height: 8),
              StatefulBuilder(
                builder: (context, setDialogState) {
                  return DropdownButtonFormField<String>(
                    initialValue: selectedClasseId,
                    decoration: const InputDecoration(labelText: 'Classe'),
                    items: _classes.map<DropdownMenuItem<String>>((c) {
                      return DropdownMenuItem(
                        value: c['id'].toString(),
                        child: Text(c['nom']),
                      );
                    }).toList(),
                    onChanged: (val) {
                      setDialogState(() => selectedClasseId = val);
                    },
                  );
                },
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Annuler'),
          ),
          FilledButton(
            onPressed: () async {
              if (nomCtrl.text.isEmpty ||
                  prenomCtrl.text.isEmpty ||
                  emailCtrl.text.isEmpty ||
                  passwordCtrl.text.isEmpty ||
                  selectedClasseId == null) {
                return;
              }
              await ApiService.addEtudiant({
                'nom': nomCtrl.text,
                'prenom': prenomCtrl.text,
                'email': emailCtrl.text,
                'password': passwordCtrl.text,
                'classe_id': int.parse(selectedClasseId!),
              });
              if (ctx.mounted) Navigator.pop(ctx);
              _refresh();
            },
            child: const Text('Ajouter'),
          ),
        ],
      ),
    );
  }
  void _showEditDialog(Map<String, dynamic> etudiant) {
    final nomCtrl = TextEditingController(text: etudiant['nom']);
    final prenomCtrl = TextEditingController(text: etudiant['prenom']);
    final emailCtrl = TextEditingController(text: etudiant['email']);
    String? selectedClasseId = etudiant['classe_id']?.toString();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Modifier l\'étudiant'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: nomCtrl,
                decoration: const InputDecoration(labelText: 'Nom'),
              ),
              TextField(
                controller: prenomCtrl,
                decoration: const InputDecoration(labelText: 'Prénom'),
              ),
              TextField(
                controller: emailCtrl,
                decoration: const InputDecoration(labelText: 'Email'),
              ),
              const SizedBox(height: 8),
              StatefulBuilder(
                builder: (context, setDialogState) {
                  return DropdownButtonFormField<String>(
                    initialValue: selectedClasseId,
                    decoration: const InputDecoration(labelText: 'Classe'),
                    items: _classes.map<DropdownMenuItem<String>>((c) {
                      return DropdownMenuItem(
                        value: c['id'].toString(),
                        child: Text(c['nom']),
                      );
                    }).toList(),
                    onChanged: (val) {
                      setDialogState(() => selectedClasseId = val);
                    },
                  );
                },
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Annuler'),
          ),
          FilledButton(
            onPressed: () async {
              await ApiService.updateEtudiant({
                'id': int.parse(etudiant['id'].toString()),
                'nom': nomCtrl.text,
                'prenom': prenomCtrl.text,
                'email': emailCtrl.text,
                'classe_id': int.parse(selectedClasseId!),
              });
              if (ctx.mounted) Navigator.pop(ctx);
              _refresh();
            },
            child: const Text('Enregistrer'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: FutureBuilder<List<dynamic>>(
        future: _futureEtudiants,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snapshot.hasError) {
            return Center(child: Text('Erreur: ${snapshot.error}'));
          }

          final etudiants = snapshot.data ?? [];
          final filteredEtudiants = etudiants.where((e) {
            if (_searchQuery.trim().isEmpty) return true;
            final q = _searchQuery.toLowerCase();
            final text =
                '${e['nom'] ?? ''} ${e['prenom'] ?? ''} ${e['email'] ?? ''} ${e['classe'] ?? ''}'
                    .toLowerCase();
            return text.contains(q);
          }).toList();

          if (etudiants.isEmpty) {
            return const Center(child: Text('Aucun étudiant'));
          }

          return Column(
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(12, 12, 12, 4),
                child: TextField(
                  controller: _searchCtrl,
                  onChanged: (value) {
                    setState(() {
                      _searchQuery = value;
                    });
                  },
                  decoration: InputDecoration(
                    hintText: 'Rechercher un étudiant...',
                    prefixIcon: const Icon(Icons.search),
                    suffixIcon: _searchQuery.isEmpty
                        ? null
                        : IconButton(
                            icon: const Icon(Icons.clear),
                            onPressed: () {
                              _searchCtrl.clear();
                              setState(() {
                                _searchQuery = '';
                              });
                            },
                          ),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
              ),
              Expanded(
                child: filteredEtudiants.isEmpty
                    ? const Center(child: Text('Aucun étudiant trouvé'))
                    : ListView.builder(
                        padding: const EdgeInsets.all(8),
                        itemCount: filteredEtudiants.length,
                        itemBuilder: (context, index) {
                          final e = filteredEtudiants[index];
                          return Card(
                            child: ListTile(
                              leading: CircleAvatar(
                                child: Text(e['nom'][0].toUpperCase()),
                              ),
                              title: Text('${e['nom']} ${e['prenom']}'),
                              subtitle: Text(e['classe'] ?? ''),
                              trailing: IconButton(
                                icon: const Icon(
                                  Icons.delete_outline,
                                  color: Colors.red,
                                ),
                                onPressed: () async {
                                  final confirm = await showDialog<bool>(
                                    context: context,
                                    builder: (ctx) => AlertDialog(
                                      title: const Text('Confirmer'),
                                      content: const Text(
                                        'Supprimer cet étudiant ?',
                                      ),
                                      actions: [
                                        TextButton(
                                          onPressed: () =>
                                              Navigator.pop(ctx, false),
                                          child: const Text('Non'),
                                        ),
                                        FilledButton(
                                          onPressed: () =>
                                              Navigator.pop(ctx, true),
                                          child: const Text('Oui'),
                                        ),
                                      ],
                                    ),
                                  );
                                  if (confirm == true) {
                                    await ApiService.deleteEtudiant(
                                      int.parse(e['id'].toString()),
                                    );
                                    _refresh();
                                  }
                                },
                              ),
                              onTap: () => _showEditDialog(e),
                            ),
                          );
                        },
                      ),
              ),
            ],
          );
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: _showAddDialog,
        child: const Icon(Icons.add),
      ),
    );
  }
}
